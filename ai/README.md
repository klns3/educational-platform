# AI analytics module

This module implements a hybrid educational analytics pipeline for the Laravel
application. It is intentionally separated from PHP: Laravel prepares student
and test features, then starts Python scripts that run machine-learning and
expert analytics.

## What can be defended as ML

The module uses real `scikit-learn` algorithms:

- `DecisionTreeClassifier` classifies the student risk level.
- `LogisticRegression` estimates the probability of successful learning.
- `KMeans` groups students by similar learning behavior.

The trained classifiers are saved as `joblib` files in `ai/models/` and reused
while the training dataset signature stays the same.

## Input features

The ML models use these numeric features:

- `average_percent` - average test completion percent.
- `attempts_count` - number of test attempts.
- `completion_percent` - percent of assigned tests completed.
- `days_since_last_attempt` - inactivity interval.
- `support_tickets_count` - number of support requests.
- `failed_attempts_count` - attempts below the target threshold.
- `score_trend` - recent result trend compared with earlier attempts.
- `activity_score` - engineered activity index.

## Output

For each student the module returns:

- risk level: low, medium, or high;
- success probability;
- behavior cluster;
- risk factors;
- recommendation for the teacher.

For tests the module estimates practical difficulty from completion results and
failed-attempt share.

## Training data

The baseline training dataset is stored in:

```bash
ai/datasets/student_training.csv
```

It contains anonymized synthetic/proxy educational records. The current system
does not yet have a verified historical target such as "completed course" or
"dropped out", so risk labels are proxy labels derived from educational
indicators. This is acceptable for a diploma prototype, but it should be stated
as a limitation.

The strongest future improvement is to replace proxy labels with real historical
outcomes:

- `completed_course`;
- `passed_final_test`;
- `final_grade`;
- `dropped_out`.

## Commands

Install dependencies:

```bash
pip install -r requirements.txt
```

Train and cache models:

```bash
python ai/train.py
```

Run framework-level diagnostics:

```bash
php artisan ai:analytics-check
```

Run analysis manually:

```bash
python ai/analyze.py storage/app/ai/input/example.json storage/app/ai/output/example.json
```

## Defense wording

Recommended wording:

> The system implements a hybrid intelligent analytics module. Machine-learning
> algorithms classify educational risk, estimate success probability and cluster
> students, while expert rules generate interpretable recommendations for the
> teacher.

Avoid calling this a neural network or deep-learning system.
