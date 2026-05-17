<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class AiAnalyticsService
{
    public function analyze(array $studentsData, array $testsData): array
    {
        $runId = (string) Str::uuid();
        $inputPath = storage_path("app/ai/input/{$runId}.json");
        $outputPath = storage_path("app/ai/output/{$runId}.json");

        File::ensureDirectoryExists(dirname($inputPath));
        File::ensureDirectoryExists(dirname($outputPath));

        try {
            File::put($inputPath, json_encode([
                'students' => $studentsData,
                'tests' => $testsData,
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
        } catch (\JsonException $exception) {
            return $this->errorResult('Ошибка подготовки данных для ИИ-модуля', $exception->getMessage());
        }

        $process = new Process($this->buildPythonCommand($inputPath, $outputPath));
        $process->setEnv($this->buildProcessEnvironment());
        $process->setTimeout(60);
        $process->run();

        if (!$process->isSuccessful() || !File::exists($outputPath)) {
            return $this->errorResult(
                'Ошибка запуска ИИ-модуля',
                trim($process->getErrorOutput() ?: $process->getOutput() ?: 'Python-скрипт не вернул результат.')
            );
        }

        try {
            $result = json_decode(File::get($outputPath), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            return $this->errorResult(
                'Ошибка чтения результата ИИ-модуля',
                'Файл result.json создан, но его не удалось корректно прочитать: ' . $exception->getMessage()
            );
        } finally {
            File::delete($inputPath);
            File::delete($outputPath);
        }

        if (!is_array($result)) {
            return $this->errorResult(
                'Ошибка чтения результата ИИ-модуля',
                'Python-скрипт вернул некорректную структуру данных.'
            );
        }

        return $this->normalizeResult($result);
    }

    private function buildPythonCommand(string $inputPath, string $outputPath): array
    {
        $scriptPath = str_replace('\\', '/', base_path('ai/analyze.py'));
        $pythonPath = trim((string) env('PYTHON_PATH', 'python'));
        $pythonVersion = trim((string) env('PYTHON_VERSION', ''));

        return array_values(array_filter([
            $pythonPath !== '' ? $pythonPath : 'python',
            $pythonVersion !== '' ? $pythonVersion : null,
            $scriptPath,
            $inputPath,
            $outputPath,
        ], fn ($part) => is_string($part) && $part !== ''));
    }

    private function buildProcessEnvironment(): array
    {
        return array_filter([
            'SystemRoot' => getenv('SystemRoot') ?: ($_SERVER['SystemRoot'] ?? null),
            'WINDIR' => getenv('WINDIR') ?: ($_SERVER['WINDIR'] ?? null),
            'PATH' => getenv('PATH') ?: ($_SERVER['PATH'] ?? ($_SERVER['Path'] ?? null)),
            'PYTHONUTF8' => '1',
            'PYTHONIOENCODING' => 'utf-8',
        ], fn ($value) => is_string($value) && $value !== '');
    }

    private function normalizeResult(array $result): array
    {
        return [
            'students' => is_array($result['students'] ?? null) ? $result['students'] : [],
            'tests' => is_array($result['tests'] ?? null) ? $result['tests'] : [],
            'recommendations' => is_array($result['recommendations'] ?? null) ? $result['recommendations'] : [],
            'methods' => is_array($result['methods'] ?? null) ? $result['methods'] : [],
            'model_quality' => is_array($result['model_quality'] ?? null)
                ? $result['model_quality']
                : $this->defaultModelQuality(),
        ];
    }

    private function errorResult(string $title, string $description): array
    {
        return [
            'students' => [],
            'tests' => [],
            'recommendations' => [
                [
                    'type' => 'danger',
                    'title' => $title,
                    'description' => $description !== '' ? $description : 'Не удалось выполнить анализ данных.',
                ],
            ],
            'methods' => [],
            'model_quality' => [
                'mode' => 'expert',
                'students_count' => 0,
                'samples_count' => 0,
                'accuracy' => null,
                'precision' => null,
                'recall' => null,
                'f1_score' => null,
                'explanation' => 'Анализ не выполнен из-за ошибки запуска или чтения результата Python-модуля.',
            ],
        ];
    }

    private function defaultModelQuality(): array
    {
        return [
            'mode' => 'expert',
            'students_count' => 0,
            'samples_count' => 0,
            'accuracy' => null,
            'precision' => null,
            'recall' => null,
            'f1_score' => null,
            'explanation' => 'Информация о качестве модели не была возвращена Python-модулем.',
        ];
    }
}
