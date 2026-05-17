import json
import sys
from pathlib import Path

import numpy as np
import pandas as pd
from sklearn.cluster import KMeans
from sklearn.linear_model import LogisticRegression
from sklearn.metrics import accuracy_score, precision_recall_fscore_support
from sklearn.model_selection import train_test_split
from sklearn.preprocessing import StandardScaler
from sklearn.tree import DecisionTreeClassifier


RISK_NAMES = {
    0: "Низкий риск",
    1: "Средний риск",
    2: "Высокий риск",
}

CLUSTER_DESCRIPTIONS = {
    "successful_active": "Активные и успешные студенты",
    "active_low_results": "Активные, но с низкими результатами",
    "successful_low_activity": "Успешные, но недостаточно активные",
    "passive_problematic": "Пассивные или проблемные студенты",
}


def load_json(path: str) -> dict:
    with open(path, "r", encoding="utf-8-sig") as file:
        data = json.load(file)

    if not isinstance(data, dict):
        return {}

    return data


def save_json(path: str, data: dict) -> None:
    Path(path).parent.mkdir(parents=True, exist_ok=True)

    with open(path, "w", encoding="utf-8") as file:
        json.dump(data, file, ensure_ascii=False, indent=4)


def empty_model_quality(students_count: int, explanation: str) -> dict:
    return {
        "mode": "expert",
        "students_count": students_count,
        "samples_count": students_count,
        "accuracy": None,
        "precision": None,
        "recall": None,
        "f1_score": None,
        "explanation": explanation,
    }


def activity_score(row) -> int:
    score = min(60, int(row["attempts_count"]) * 5)
    days = int(row["days_since_last_attempt"])

    if days <= 3:
        score += 40
    elif days <= 7:
        score += 30
    elif days <= 14:
        score += 20
    elif days <= 30:
        score += 10

    return min(100, score)


def make_training_risk_label(row) -> int:
    """
    Учебная разметка риска для ограниченной выборки.

    В проекте нет исторической production-разметки вида "студент действительно
    не завершил обучение" или "студент успешно завершил курс". Поэтому целевая
    переменная формируется из нескольких образовательных факторов: результата,
    завершённости тестов, неактивности, отрицательной динамики и провалов.
    Это честнее, чем простое правило average_percent >= 70, но всё равно остаётся
    учебной proxy-разметкой, а не промышленной исторической меткой.
    """
    if int(row["attempts_count"]) == 0:
        return 2

    risk_points = 0

    if float(row["average_percent"]) < 50:
        risk_points += 2
    elif float(row["average_percent"]) < 70:
        risk_points += 1

    if float(row["completion_percent"]) < 35:
        risk_points += 2
    elif float(row["completion_percent"]) < 65:
        risk_points += 1

    if int(row["days_since_last_attempt"]) > 21:
        risk_points += 2
    elif int(row["days_since_last_attempt"]) > 10:
        risk_points += 1

    if float(row["score_trend"]) < -15:
        risk_points += 2
    elif float(row["score_trend"]) < -7:
        risk_points += 1

    if int(row["failed_attempts_count"]) >= 3:
        risk_points += 2
    elif int(row["failed_attempts_count"]) >= 1:
        risk_points += 1

    if risk_points >= 4:
        return 2

    if risk_points >= 2:
        return 1

    return 0


def risk_name(label: int) -> str:
    return RISK_NAMES.get(int(label), "Средний риск")


def category_name(row) -> str:
    if int(row["risk_label"]) == 2:
        return "Группа риска"

    if float(row["average_percent"]) >= 90 and float(row["completion_percent"]) >= 85:
        return "Сильный студент"

    if float(row["average_percent"]) >= 70:
        return "Стабильный студент"

    return "Нестабильный студент"


def expert_success_probability(row) -> int:
    value = 0
    value += float(row["average_percent"]) * 0.40
    value += float(row["completion_percent"]) * 0.25
    value += float(row["activity_score"]) * 0.20
    value += max(0, 10 - min(10, int(row["failed_attempts_count"]) * 2))
    value += min(5, int(row["attempts_count"]))

    if float(row["score_trend"]) > 0:
        value += min(5, float(row["score_trend"]) / 2)

    if float(row["score_trend"]) < -10:
        value -= 8

    if int(row["days_since_last_attempt"]) > 21:
        value -= 10

    return int(max(0, min(100, round(value))))


def recommendation(row) -> str:
    risk_level = risk_name(int(row["risk_label"]))
    trend = float(row["score_trend"])
    completion = float(row["completion_percent"])
    inactive_days = int(row["days_since_last_attempt"])
    tickets = int(row["support_tickets_count"])
    cluster_description = str(row.get("cluster_description", ""))

    if risk_level == "Высокий риск":
        return (
            "Рекомендуется индивидуальная образовательная траектория: провести консультацию, "
            "повторить ключевые материалы и назначить контрольные задания с последующей проверкой динамики."
        )

    if trend < -10:
        return (
            "Зафиксировано снижение результатов. Целесообразно проанализировать последние ошибки, "
            "сравнить их с темами курса и дать адресную обратную связь."
        )

    if completion < 50:
        return (
            "Завершённость тестов ниже ожидаемого уровня. Рекомендуется усилить контроль прохождения "
            "заданий и напомнить студенту о недостающих активностях."
        )

    if inactive_days > 14:
        return (
            "Студент длительное время не проявлял учебной активности. Рекомендуется направить уведомление "
            "и уточнить причины отсутствия прогресса."
        )

    if tickets >= 3:
        return (
            "Частые обращения в поддержку могут указывать на методические или технические затруднения. "
            "Рекомендуется дополнительное сопровождение и проверка доступности материалов."
        )

    if cluster_description == CLUSTER_DESCRIPTIONS["active_low_results"]:
        return (
            "Студент активен, но демонстрирует недостаточные результаты. Рекомендуется разобрать типовые "
            "ошибки и предложить тренировочные задания по проблемным темам."
        )

    if cluster_description == CLUSTER_DESCRIPTIONS["successful_low_activity"]:
        return (
            "Результаты достаточные, однако активность снижена. Рекомендуется поддержать регулярность "
            "работы через короткие промежуточные задания."
        )

    if float(row["average_percent"]) >= 90:
        return (
            "Студент демонстрирует высокий уровень освоения материала. Можно предложить задания повышенной "
            "сложности или проектную работу."
        )

    return "Студент показывает стабильную учебную динамику. Рекомендуется продолжать текущий формат сопровождения."


def model_quality(mode: str, df: pd.DataFrame, metrics: dict | None = None) -> dict:
    if mode == "ml" and metrics is not None:
        return {
            "mode": "ml",
            "students_count": int(len(df)),
            "samples_count": int(len(df)),
            "accuracy": metrics["accuracy"],
            "precision": metrics["precision"],
            "recall": metrics["recall"],
            "f1_score": metrics["f1_score"],
            "explanation": (
                "Используется ML-режим: риск классифицируется деревом решений, обученным на учебной "
                "многофакторной разметке. Метрики рассчитаны на отложенной части текущей выборки."
            ),
        }

    return empty_model_quality(
        len(df),
        "Недостаточно данных или классов риска для устойчивого обучения модели. Используется экспертный режим на основе правил.",
    )


def prepare_students_frame(students: list) -> pd.DataFrame:
    df = pd.DataFrame(students)

    numeric_columns = [
        "average_score",
        "average_percent",
        "best_score",
        "attempts_count",
        "failed_attempts_count",
        "completed_tests_count",
        "assigned_tests_count",
        "completion_percent",
        "days_since_last_attempt",
        "support_tickets_count",
        "score_trend",
    ]

    for column in numeric_columns:
        if column not in df.columns:
            df[column] = 0

        df[column] = pd.to_numeric(df[column], errors="coerce").fillna(0)

    if "id" not in df.columns:
        df["id"] = range(1, len(df) + 1)

    if "name" not in df.columns:
        df["name"] = "Студент"

    if "group_name" not in df.columns:
        df["group_name"] = "Без группы"

    df["activity_score"] = df.apply(activity_score, axis=1)
    df["training_risk_label"] = df.apply(make_training_risk_label, axis=1)

    return df


def feature_columns() -> list[str]:
    return [
        "average_percent",
        "attempts_count",
        "completion_percent",
        "days_since_last_attempt",
        "support_tickets_count",
        "failed_attempts_count",
        "score_trend",
        "activity_score",
    ]


def classify_risk(df: pd.DataFrame, features: pd.DataFrame) -> tuple[pd.DataFrame, dict]:
    enough_samples = len(df) >= 8
    enough_classes = df["training_risk_label"].nunique() >= 2

    if not enough_samples or not enough_classes:
        df["risk_label"] = df["training_risk_label"]
        return df, model_quality("expert", df)

    y = df["training_risk_label"]
    stratify = y if y.value_counts().min() >= 2 else None

    try:
        x_train, x_test, y_train, y_test = train_test_split(
            features,
            y,
            test_size=0.3,
            random_state=42,
            stratify=stratify,
        )

        classifier = DecisionTreeClassifier(max_depth=4, min_samples_leaf=2, random_state=42)
        classifier.fit(x_train, y_train)
        y_pred = classifier.predict(x_test)

        precision, recall, f1, _ = precision_recall_fscore_support(
            y_test,
            y_pred,
            average="weighted",
            zero_division=0,
        )

        metrics = {
            "accuracy": round(float(accuracy_score(y_test, y_pred)), 4),
            "precision": round(float(precision), 4),
            "recall": round(float(recall), 4),
            "f1_score": round(float(f1), 4),
        }

        classifier.fit(features, y)
        df["risk_label"] = classifier.predict(features)

        return df, model_quality("ml", df, metrics)
    except Exception:
        df["risk_label"] = df["training_risk_label"]
        return df, model_quality("expert", df)


def cluster_key(cluster_row) -> str:
    average = float(cluster_row["average_percent"])
    activity = float(cluster_row["activity_score"])
    completion = float(cluster_row["completion_percent"])
    failed = float(cluster_row["failed_attempts_count"])

    if average >= 75 and activity >= 60 and completion >= 65 and failed < 2:
        return "successful_active"

    if activity >= 60 and (average < 70 or failed >= 2):
        return "active_low_results"

    if average >= 70 and activity < 60:
        return "successful_low_activity"

    return "passive_problematic"


def assign_clusters(df: pd.DataFrame, features: pd.DataFrame) -> pd.DataFrame:
    if len(df) < 4:
        df["cluster_id"] = 0
        df["cluster_name"] = "Кластер 1"
        df["cluster_description"] = CLUSTER_DESCRIPTIONS[cluster_key(df.mean(numeric_only=True))]
        return df

    scaled_features = StandardScaler().fit_transform(features)
    clusters_count = min(4, len(df))

    kmeans = KMeans(n_clusters=clusters_count, random_state=42, n_init=10)
    df["cluster_id"] = kmeans.fit_predict(scaled_features)

    cluster_profiles = (
        df.groupby("cluster_id")[
            ["average_percent", "activity_score", "completion_percent", "failed_attempts_count"]
        ]
        .mean()
        .reset_index()
    )

    profile_map = {}
    for index, profile in cluster_profiles.iterrows():
        key = cluster_key(profile)
        profile_map[int(profile["cluster_id"])] = {
            "name": f"Кластер {index + 1}",
            "description": CLUSTER_DESCRIPTIONS[key],
        }

    df["cluster_name"] = df["cluster_id"].map(lambda cluster_id: profile_map[int(cluster_id)]["name"])
    df["cluster_description"] = df["cluster_id"].map(lambda cluster_id: profile_map[int(cluster_id)]["description"])

    return df


def predict_success(df: pd.DataFrame, features: pd.DataFrame) -> pd.DataFrame:
    success_target = (df["training_risk_label"] == 0).astype(int)

    if len(df) >= 8 and success_target.nunique() >= 2:
        try:
            model = LogisticRegression(max_iter=1000)
            model.fit(features, success_target)
            df["success_probability"] = (model.predict_proba(features)[:, 1] * 100).round().astype(int)
            df["prediction_method"] = "logistic_regression"
            return df
        except Exception:
            pass

    df["success_probability"] = df.apply(expert_success_probability, axis=1)
    df["prediction_method"] = "expert_formula"
    return df


def analyze_students(students: list) -> tuple[list, dict]:
    if not students:
        return [], empty_model_quality(0, "Нет данных о студентах. Аналитика работает в экспертном режиме без расчёта метрик.")

    df = prepare_students_frame(students)
    features = df[feature_columns()]

    df, quality = classify_risk(df, features)
    df = assign_clusters(df, features)
    df = predict_success(df, features)

    result = []

    for _, row in df.iterrows():
        result.append({
            "id": int(row["id"]),
            "name": str(row["name"]),
            "group_name": row.get("group_name", "Без группы"),

            "average_score": float(row.get("average_score", row["average_percent"])),
            "average_percent": float(row["average_percent"]),
            "best_score": float(row.get("best_score", 0)),

            "attempts_count": int(row["attempts_count"]),
            "failed_attempts_count": int(row["failed_attempts_count"]),

            "completed_tests_count": int(row.get("completed_tests_count", 0)),
            "assigned_tests_count": int(row.get("assigned_tests_count", 0)),
            "completion_percent": float(row["completion_percent"]),

            "days_since_last_attempt": int(row["days_since_last_attempt"]),
            "support_tickets_count": int(row["support_tickets_count"]),
            "score_trend": float(row["score_trend"]),

            "activity_score": int(row["activity_score"]),
            "success_probability": int(row["success_probability"]),
            "prediction_method": str(row["prediction_method"]),

            "risk_level": risk_name(int(row["risk_label"])),
            "category": category_name(row),

            "cluster": int(row["cluster_id"]),
            "cluster_name": str(row["cluster_name"]),
            "cluster_description": str(row["cluster_description"]),

            "recommendation": recommendation(row),
        })

    return result, quality


def test_difficulty_name(average_percent: float) -> str:
    if average_percent < 50:
        return "Сложный"

    if average_percent < 75:
        return "Средний"

    return "Лёгкий"


def test_recommendation(difficulty: str, average_percent: float, failed_percent: float) -> str:
    if difficulty == "Сложный":
        return "Рекомендуется повторить тему, проверить формулировки вопросов и провести разбор типовых ошибок."

    if average_percent > 95:
        return "Тест может быть слишком лёгким. Целесообразно добавить вопросы повышенного уровня сложности."

    if failed_percent > 50:
        return "У значительной части студентов возникают затруднения. Рекомендуется повторно разобрать тему на занятии."

    return "Тест показывает сбалансированный уровень сложности."


def analyze_tests(tests: list) -> list:
    result = []

    for test in tests:
        average_percent = float(test.get("average_percent", 0))
        failed_percent = float(test.get("failed_percent", 0))
        difficulty = test_difficulty_name(average_percent)

        result.append({
            "test": {
                "id": test.get("id"),
                "title": test.get("title"),
                "course": {
                    "title": test.get("course_title", "Без курса")
                }
            },
            "attempts_count": int(test.get("attempts_count", 0)),
            "average_percent": average_percent,
            "failed_percent": failed_percent,
            "difficulty_level": difficulty,
            "recommendation": test_recommendation(difficulty, average_percent, failed_percent),
        })

    return sorted(result, key=lambda item: item["average_percent"])


def build_recommendations(students: list, tests: list, quality: dict) -> list:
    recommendations = []

    high_risk_count = sum(1 for student in students if student["risk_level"] == "Высокий риск")
    inactive_count = sum(1 for student in students if student["days_since_last_attempt"] > 14)
    hard_tests_count = sum(1 for test in tests if test["difficulty_level"] == "Сложный")
    falling_count = sum(1 for student in students if student["score_trend"] < -10)
    support_count = sum(1 for student in students if student["support_tickets_count"] >= 3)
    expert_mode = quality.get("mode") == "expert"

    if expert_mode:
        recommendations.append({
            "type": "info",
            "title": "Используется экспертный режим",
            "description": quality.get("explanation", "Модель работает на правилах из-за ограниченного объёма данных."),
        })

    if high_risk_count > 0:
        recommendations.append({
            "type": "danger",
            "title": "Обнаружены студенты группы риска",
            "description": f"Количество студентов с высоким риском: {high_risk_count}. Рекомендуется организовать индивидуальное сопровождение.",
        })

    if inactive_count > 0:
        recommendations.append({
            "type": "warning",
            "title": "Выявлена длительная неактивность",
            "description": f"Студентов без активности более 14 дней: {inactive_count}. Следует уточнить причины отсутствия прогресса.",
        })

    if falling_count > 0:
        recommendations.append({
            "type": "warning",
            "title": "Обнаружено снижение успеваемости",
            "description": f"У {falling_count} студентов наблюдается отрицательная динамика результатов. Рекомендуется анализ последних ошибок.",
        })

    if support_count > 0:
        recommendations.append({
            "type": "info",
            "title": "Есть частые обращения в поддержку",
            "description": f"У {support_count} студентов много обращений. Возможно, требуется методическая или техническая помощь.",
        })

    if hard_tests_count > 0:
        recommendations.append({
            "type": "info",
            "title": "Выявлены сложные тесты",
            "description": f"Количество сложных тестов: {hard_tests_count}. Рекомендуется повторить соответствующие темы и проверить вопросы.",
        })

    if not recommendations:
        recommendations.append({
            "type": "success",
            "title": "Критических проблем не обнаружено",
            "description": "Интеллектуальная аналитика не выявила выраженных образовательных рисков.",
        })

    return recommendations


def main() -> None:
    if len(sys.argv) < 3:
        raise RuntimeError("Usage: python analyze.py input.json output.json")

    input_path = sys.argv[1]
    output_path = sys.argv[2]

    data = load_json(input_path)
    raw_students = data.get("students", [])
    raw_tests = data.get("tests", [])

    if not isinstance(raw_students, list):
        raw_students = []

    if not isinstance(raw_tests, list):
        raw_tests = []

    students, quality = analyze_students(raw_students)
    tests = analyze_tests(raw_tests)
    recommendations = build_recommendations(students, tests, quality)

    save_json(output_path, {
        "students": students,
        "tests": tests,
        "recommendations": recommendations,
        "model_quality": quality,
        "methods": [
            "K-Means clustering",
            "Decision Tree classification",
            "Logistic Regression prediction",
            "Feature engineering",
            "Expert recommendation system",
            "Test difficulty analysis",
        ],
    })


if __name__ == "__main__":
    main()
