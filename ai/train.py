import json
from pathlib import Path

import joblib
import numpy as np
from sklearn.linear_model import LogisticRegression
from sklearn.metrics import accuracy_score, precision_recall_fscore_support
from sklearn.model_selection import train_test_split
from sklearn.tree import DecisionTreeClassifier

from analyze import (
    RISK_MODEL_PATH,
    SUCCESS_MODEL_PATH,
    confusion_matrix_payload,
    cross_validation_metrics,
    feature_columns,
    feature_importance_payload,
    load_training_dataset,
    training_signature,
)


def train_risk_classifier(dataset):
    features = dataset[feature_columns()]
    target = dataset["risk_label"].astype(int)

    if len(features) < 12 or target.nunique() < 2:
        raise RuntimeError("Not enough training data or risk classes for classifier training.")

    stratify = target if target.value_counts().min() >= 2 else None
    x_train, x_test, y_train, y_test = train_test_split(
        features,
        target,
        test_size=0.3,
        random_state=42,
        stratify=stratify,
    )

    validation_model = DecisionTreeClassifier(max_depth=4, min_samples_leaf=2, random_state=42)
    validation_model.fit(x_train, y_train)
    y_pred = validation_model.predict(x_test)

    precision, recall, f1, _ = precision_recall_fscore_support(
        y_test,
        y_pred,
        average="weighted",
        zero_division=0,
    )

    model = DecisionTreeClassifier(max_depth=4, min_samples_leaf=2, random_state=42)
    model.fit(features, target)

    signature = training_signature(features, target)
    RISK_MODEL_PATH.parent.mkdir(parents=True, exist_ok=True)
    joblib.dump({"signature": signature, "model": model}, RISK_MODEL_PATH)

    return {
        "path": str(RISK_MODEL_PATH),
        "samples": int(len(features)),
        "classes": sorted(int(value) for value in target.unique()),
        "accuracy": round(float(accuracy_score(y_test, y_pred)), 4),
        "precision": round(float(precision), 4),
        "recall": round(float(recall), 4),
        "f1_score": round(float(f1), 4),
        "confusion_matrix": confusion_matrix_payload(y_test, y_pred),
        "feature_importance": feature_importance_payload(model),
        **cross_validation_metrics(features, target),
    }


def train_success_predictor(dataset):
    features = dataset[feature_columns()]
    target = (dataset["risk_label"].astype(int) == 0).astype(int)

    if len(features) < 12 or target.nunique() < 2:
        raise RuntimeError("Not enough training data or success classes for predictor training.")

    model = LogisticRegression(max_iter=1000)
    model.fit(features, target)

    signature = training_signature(features, target)
    SUCCESS_MODEL_PATH.parent.mkdir(parents=True, exist_ok=True)
    joblib.dump({"signature": signature, "model": model}, SUCCESS_MODEL_PATH)

    probabilities = model.predict_proba(features)[:, 1]

    return {
        "path": str(SUCCESS_MODEL_PATH),
        "samples": int(len(features)),
        "positive_class_share": round(float(np.mean(target)), 4),
        "average_success_probability": round(float(np.mean(probabilities)), 4),
    }


def main() -> None:
    dataset = load_training_dataset()

    if dataset is None or dataset.empty:
        raise RuntimeError("Training dataset was not found or is empty.")

    report = {
        "dataset": {
            "path": str(Path("ai/datasets/student_training.csv")),
            "samples": int(len(dataset)),
            "class_distribution": {
                str(int(label)): int(count)
                for label, count in dataset["risk_label"].value_counts().sort_index().items()
            },
            "features": feature_columns(),
        },
        "risk_classifier": train_risk_classifier(dataset),
        "success_predictor": train_success_predictor(dataset),
    }

    print(json.dumps(report, ensure_ascii=False, indent=2))


if __name__ == "__main__":
    main()
