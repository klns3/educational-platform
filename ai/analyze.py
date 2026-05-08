import json
import sys
from pathlib import Path

import numpy as np
import pandas as pd
from sklearn.cluster import KMeans
from sklearn.preprocessing import StandardScaler
from sklearn.tree import DecisionTreeClassifier
from sklearn.linear_model import LogisticRegression


def load_json(path: str) -> dict:
    with open(path, "r", encoding="utf-8") as file:
        return json.load(file)


def save_json(path: str, data: dict) -> None:
    Path(path).parent.mkdir(parents=True, exist_ok=True)

    with open(path, "w", encoding="utf-8") as file:
        json.dump(data, file, ensure_ascii=False, indent=4)


def activity_score(row) -> int:
    score = 0

    score += min(60, int(row["attempts_count"]) * 5)

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


def make_base_risk_label(row) -> int:
    """
    0 — низкий риск
    1 — средний риск
    2 — высокий риск

    Это нужно как учебная разметка для дерева решений.
    Само дерево потом обучается на признаках и воспроизводит классификацию.
    """
    if row["attempts_count"] == 0:
        return 2

    if (
        row["average_percent"] < 50
        or row["completion_percent"] < 30
        or row["days_since_last_attempt"] > 14
        or row["failed_attempts_count"] >= 3
    ):
        return 2

    if (
        row["average_percent"] < 70
        or row["completion_percent"] < 60
        or row["days_since_last_attempt"] > 7
    ):
        return 1

    return 0


def risk_name(label: int) -> str:
    return {
        0: "Низкий риск",
        1: "Средний риск",
        2: "Высокий риск",
    }.get(int(label), "Средний риск")


def category_name(row) -> str:
    if row["risk_label"] == 2:
        return "Группа риска"

    if row["average_percent"] >= 90 and row["completion_percent"] >= 85:
        return "Сильный студент"

    if row["average_percent"] >= 70:
        return "Стабильный студент"

    return "Нестабильный студент"


def recommendation(row) -> str:
    if row["risk_label"] == 2:
        return "Рекомендуется индивидуальная консультация и повторение учебных материалов."

    if row["completion_percent"] < 50:
        return "Рекомендуется увеличить активность прохождения тестов."

    if row["support_tickets_count"] >= 3:
        return "Студент часто обращается в поддержку. Возможно, требуется дополнительное сопровождение."

    if row["average_percent"] >= 90:
        return "Можно предложить задания повышенной сложности."

    if row["score_trend"] < -10:
        return "Обнаружено снижение результата. Рекомендуется проверить причины падения успеваемости."

    return "Студент показывает стабильные результаты."


def cluster_name(cluster_id: int, row) -> str:
    if row["average_percent"] >= 75 and row["activity_score"] >= 65:
        return "Кластер 1"

    if row["average_percent"] < 70 and row["activity_score"] >= 65:
        return "Кластер 2"

    if row["average_percent"] >= 70 and row["activity_score"] < 65:
        return "Кластер 3"

    return "Кластер 4"


def cluster_description(name: str) -> str:
    return {
        "Кластер 1": "Активные и успешные студенты",
        "Кластер 2": "Активные, но с низкими результатами",
        "Кластер 3": "Успешные, но недостаточно активные",
        "Кластер 4": "Пассивные или проблемные студенты",
    }.get(name, "Смешанная группа студентов")


def success_probability(row) -> int:
    value = 0

    value += float(row["average_percent"]) * 0.45
    value += float(row["completion_percent"]) * 0.25
    value += float(row["activity_score"]) * 0.20
    value += min(10, int(row["attempts_count"]))

    if row["score_trend"] > 0:
        value += min(5, row["score_trend"] / 2)

    if row["score_trend"] < -10:
        value -= 8

    return int(max(0, min(100, round(value))))


def analyze_students(students: list) -> list:
    if not students:
        return []

    df = pd.DataFrame(students)

    numeric_columns = [
        "average_percent",
        "attempts_count",
        "completion_percent",
        "days_since_last_attempt",
        "support_tickets_count",
        "failed_attempts_count",
        "score_trend",
    ]

    for column in numeric_columns:
        if column not in df.columns:
            df[column] = 0

        df[column] = pd.to_numeric(df[column], errors="coerce").fillna(0)

    df["activity_score"] = df.apply(activity_score, axis=1)

    features = df[
        [
            "average_percent",
            "attempts_count",
            "completion_percent",
            "days_since_last_attempt",
            "support_tickets_count",
            "failed_attempts_count",
            "score_trend",
            "activity_score",
        ]
    ]

    df["base_risk_label"] = df.apply(make_base_risk_label, axis=1)

    if len(df) >= 3 and df["base_risk_label"].nunique() >= 2:
        classifier = DecisionTreeClassifier(max_depth=4, random_state=42)
        classifier.fit(features, df["base_risk_label"])
        df["risk_label"] = classifier.predict(features)
    else:
        df["risk_label"] = df["base_risk_label"]

    if len(df) >= 4:
        scaled_features = StandardScaler().fit_transform(features)
        clusters_count = min(4, len(df))

        kmeans = KMeans(
            n_clusters=clusters_count,
            random_state=42,
            n_init=10
        )

        df["cluster_id"] = kmeans.fit_predict(scaled_features)
    else:
        df["cluster_id"] = 0

    if len(df) >= 4 and df["base_risk_label"].nunique() >= 2:
        logistic_target = (df["average_percent"] >= 70).astype(int)

        if logistic_target.nunique() >= 2:
            model = LogisticRegression(max_iter=1000)
            model.fit(features, logistic_target)
            df["success_probability"] = (
                model.predict_proba(features)[:, 1] * 100
            ).round().astype(int)
        else:
            df["success_probability"] = df.apply(success_probability, axis=1)
    else:
        df["success_probability"] = df.apply(success_probability, axis=1)

    result = []

    for _, row in df.iterrows():
        cluster = cluster_name(int(row["cluster_id"]), row)

        result.append({
            "id": int(row["id"]),
            "name": row["name"],
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

            "risk_level": risk_name(int(row["risk_label"])),
            "category": category_name(row),

            "cluster": int(row["cluster_id"]),
            "cluster_name": cluster,
            "cluster_description": cluster_description(cluster),

            "recommendation": recommendation(row),
        })

    return result


def test_difficulty_name(average_percent: float) -> str:
    if average_percent < 50:
        return "Сложный"

    if average_percent < 75:
        return "Средний"

    return "Лёгкий"


def test_recommendation(difficulty: str, average_percent: float, failed_percent: float) -> str:
    if difficulty == "Сложный":
        return "Рекомендуется повторить тему и проверить корректность вопросов."

    if average_percent > 95:
        return "Тест может быть слишком лёгким."

    if failed_percent > 50:
        return "У значительной части студентов возникают ошибки. Стоит разобрать тему на занятии."

    return "Тест показывает нормальный уровень сложности."


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


def build_recommendations(students: list, tests: list) -> list:
    recommendations = []

    high_risk_count = sum(1 for s in students if s["risk_level"] == "Высокий риск")
    inactive_count = sum(1 for s in students if s["days_since_last_attempt"] > 14)
    hard_tests_count = sum(1 for t in tests if t["difficulty_level"] == "Сложный")
    falling_count = sum(1 for s in students if s["score_trend"] < -10)
    strong_count = sum(1 for s in students if s["average_percent"] >= 90)

    if high_risk_count > 0:
        recommendations.append({
            "type": "danger",
            "title": "Обнаружены студенты группы риска",
            "description": f"Количество студентов с высоким риском: {high_risk_count}. Рекомендуется провести консультации."
        })

    if inactive_count > 0:
        recommendations.append({
            "type": "warning",
            "title": "Обнаружены неактивные студенты",
            "description": f"Студентов без активности более 14 дней: {inactive_count}. Рекомендуется отправить уведомления."
        })

    if hard_tests_count > 0:
        recommendations.append({
            "type": "info",
            "title": "Выявлены сложные тесты",
            "description": f"Количество сложных тестов: {hard_tests_count}. Рекомендуется повторить соответствующие темы."
        })

    if falling_count > 0:
        recommendations.append({
            "type": "warning",
            "title": "Обнаружено снижение успеваемости",
            "description": f"У {falling_count} студентов наблюдается отрицательная динамика результатов."
        })

    if strong_count > 0:
        recommendations.append({
            "type": "success",
            "title": "Обнаружены сильные студенты",
            "description": f"Количество студентов с высоким результатом: {strong_count}. Можно предложить задания повышенной сложности."
        })

    if not recommendations:
        recommendations.append({
            "type": "success",
            "title": "Критических проблем не обнаружено",
            "description": "ИИ-модуль не выявил выраженных образовательных рисков."
        })

    return recommendations


def main() -> None:
    if len(sys.argv) < 3:
        raise RuntimeError("Usage: python analyze.py input.json output.json")

    input_path = sys.argv[1]
    output_path = sys.argv[2]

    data = load_json(input_path)

    students = analyze_students(data.get("students", []))
    tests = analyze_tests(data.get("tests", []))
    recommendations = build_recommendations(students, tests)

    save_json(output_path, {
        "students": students,
        "tests": tests,
        "recommendations": recommendations,
        "methods": [
            "K-Means clustering",
            "Decision Tree classification",
            "Logistic Regression prediction",
            "Feature engineering",
            "Expert recommendation system",
            "Test difficulty analysis"
        ]
    })


if __name__ == "__main__":
    main()