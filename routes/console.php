<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('ai:analytics-check', function () {
    $python = trim((string) env('PYTHON_PATH', 'python')) ?: 'python';
    $pythonVersion = trim((string) env('PYTHON_VERSION', ''));

    $commandPrefix = array_values(array_filter([
        $python,
        $pythonVersion !== '' ? $pythonVersion : null,
    ]));

    $environment = array_filter([
        'SystemRoot' => getenv('SystemRoot') ?: ($_SERVER['SystemRoot'] ?? null),
        'WINDIR' => getenv('WINDIR') ?: ($_SERVER['WINDIR'] ?? null),
        'PATH' => getenv('PATH') ?: ($_SERVER['PATH'] ?? ($_SERVER['Path'] ?? null)),
        'PYTHONUTF8' => '1',
        'PYTHONIOENCODING' => 'utf-8',
    ], fn ($value) => is_string($value) && $value !== '');

    $run = function (array $command, int $timeout = 60) use ($environment) {
        $process = new Process($command, base_path());
        $process->setEnv($environment);
        $process->setTimeout($timeout);
        $process->run();

        return $process;
    };

    $this->info('Checking Python executable...');
    $versionProcess = $run([...$commandPrefix, '--version'], 15);

    if (!$versionProcess->isSuccessful()) {
        $this->error('Python is not available.');
        $this->line(trim($versionProcess->getErrorOutput() ?: $versionProcess->getOutput()));

        return self::FAILURE;
    }

    $this->line(trim($versionProcess->getOutput() ?: $versionProcess->getErrorOutput()));

    $this->info('Checking Python ML dependencies...');
    $dependencyProcess = $run([
        ...$commandPrefix,
        '-c',
        'import pandas, numpy, sklearn, joblib; print("dependencies ok")',
    ], 30);

    if (!$dependencyProcess->isSuccessful()) {
        $this->error('Required Python packages are missing. Run: pip install -r requirements.txt');
        $this->line(trim($dependencyProcess->getErrorOutput() ?: $dependencyProcess->getOutput()));

        return self::FAILURE;
    }

    $this->line(trim($dependencyProcess->getOutput()));

    $datasetPath = base_path('ai/datasets/student_training.csv');
    $this->info('Checking training dataset...');

    if (!File::exists($datasetPath)) {
        $this->error('Training dataset not found: ' . $datasetPath);

        return self::FAILURE;
    }

    $this->line('Dataset found: ' . $datasetPath);

    $this->info('Training/caching ML models...');
    $trainProcess = $run([...$commandPrefix, str_replace('\\', '/', base_path('ai/train.py'))], 120);

    if (!$trainProcess->isSuccessful()) {
        $this->error('Model training failed.');
        $this->line(trim($trainProcess->getErrorOutput() ?: $trainProcess->getOutput()));

        return self::FAILURE;
    }

    $trainReport = json_decode($trainProcess->getOutput(), true);
    $this->line('Training samples: ' . data_get($trainReport, 'dataset.samples', 'unknown'));
    $this->line('Risk accuracy: ' . data_get($trainReport, 'risk_classifier.accuracy', 'unknown'));
    $this->line('Risk F1-score: ' . data_get($trainReport, 'risk_classifier.f1_score', 'unknown'));

    $this->info('Running end-to-end analysis smoke test...');

    $runId = (string) Str::uuid();
    $inputPath = storage_path("app/ai/check/{$runId}-input.json");
    $outputPath = storage_path("app/ai/check/{$runId}-output.json");

    File::ensureDirectoryExists(dirname($inputPath));
    File::ensureDirectoryExists(dirname($outputPath));

    File::put($inputPath, json_encode([
        'students' => [
            [
                'id' => 1,
                'name' => 'Demo Student A',
                'group_name' => 'Demo',
                'average_score' => 9,
                'average_percent' => 90,
                'best_score' => 10,
                'attempts_count' => 8,
                'failed_attempts_count' => 0,
                'completed_tests_count' => 8,
                'assigned_tests_count' => 8,
                'completion_percent' => 100,
                'days_since_last_attempt' => 1,
                'support_tickets_count' => 0,
                'score_trend' => 4,
            ],
            [
                'id' => 2,
                'name' => 'Demo Student B',
                'group_name' => 'Demo',
                'average_score' => 4,
                'average_percent' => 40,
                'best_score' => 6,
                'attempts_count' => 2,
                'failed_attempts_count' => 2,
                'completed_tests_count' => 2,
                'assigned_tests_count' => 8,
                'completion_percent' => 25,
                'days_since_last_attempt' => 30,
                'support_tickets_count' => 3,
                'score_trend' => -15,
            ],
        ],
        'tests' => [
            [
                'id' => 1,
                'title' => 'Demo Test',
                'course_title' => 'Demo Course',
                'attempts_count' => 2,
                'average_percent' => 65,
                'failed_percent' => 50,
            ],
        ],
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));

    try {
        $analysisProcess = $run([
            ...$commandPrefix,
            str_replace('\\', '/', base_path('ai/analyze.py')),
            $inputPath,
            $outputPath,
        ], 60);

        if (!$analysisProcess->isSuccessful() || !File::exists($outputPath)) {
            $this->error('Analysis smoke test failed.');
            $this->line(trim($analysisProcess->getErrorOutput() ?: $analysisProcess->getOutput()));

            return self::FAILURE;
        }

        $result = json_decode(File::get($outputPath), true, 512, JSON_THROW_ON_ERROR);
    } finally {
        File::delete($inputPath);
        File::delete($outputPath);
    }

    $this->line('Analysis mode: ' . data_get($result, 'model_quality.mode', 'unknown'));
    $this->line('Students analyzed: ' . count($result['students'] ?? []));
    $this->line('Methods: ' . implode(', ', $result['methods'] ?? []));
    $this->info('AI analytics module is ready.');

    return self::SUCCESS;
})->purpose('Check Python dependencies, train ML models, and run AI analytics smoke test');
