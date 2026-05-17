<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use App\Models\Test;
use App\Models\TestAttempt;
use App\Models\User;
use App\Services\AiAnalyticsService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AiAnalyticsController extends Controller
{
    public function index(AiAnalyticsService $aiAnalyticsService)
    {
        $user = Auth::user();

        $studentsData = $this->buildStudentsData($user);
        $testsData = $this->buildTestsData($user);
        $result = $aiAnalyticsService->analyze($studentsData, $testsData);

        $studentAnalytics = $this->mapStudentAnalytics($result['students'] ?? []);
        $clusteredStudents = $studentAnalytics->groupBy('cluster_name');
        $testAnalytics = $this->mapTestAnalytics($result['tests'] ?? []);
        $expertRecommendations = collect($result['recommendations'] ?? []);
        $modelQuality = $result['model_quality'] ?? [];

        return view('ai-analytics.index', [
            'studentsCount' => $studentAnalytics->count(),

            'riskCount' => $studentAnalytics
                ->where('risk_level', 'Высокий риск')
                ->count(),

            'stableCount' => $studentAnalytics
                ->where('risk_level', 'Низкий риск')
                ->count(),

            'averageSuccessProbability' => round(
                (float) $studentAnalytics->avg('success_probability'),
                2
            ),

            'studentAnalytics' => $studentAnalytics,
            'clusteredStudents' => $clusteredStudents,
            'testAnalytics' => $testAnalytics,
            'expertRecommendations' => $expertRecommendations,
            'modelQuality' => $modelQuality,
        ]);
    }

    private function buildStudentsData(User $user): array
    {
        $studentsQuery = User::query()
            ->where('role', 'student')
            ->with('classGroup');

        if ($user->role === 'teacher') {
            $courseIds = $user->teachingCourses()->pluck('id');

            $studentIds = DB::table('course_user')
                ->whereIn('course_id', $courseIds)
                ->pluck('user_id')
                ->unique();

            $studentsQuery->whereIn('id', $studentIds);
        }

        return $studentsQuery->get()->map(function (User $student) {
            $attempts = TestAttempt::query()
                ->where('user_id', $student->id)
                ->orderBy('created_at')
                ->get();

            $attemptsCount = $attempts->count();

            $averageScore = round((float) ($attempts->avg('score') ?? 0), 2);
            $bestScore = round((float) ($attempts->max('score') ?? 0), 2);

            $attemptPercents = $attempts->map(function (TestAttempt $attempt) {
                if ($attempt->max_score <= 0) {
                    return 0;
                }

                return round(($attempt->score / $attempt->max_score) * 100, 2);
            });

            $averagePercent = round((float) ($attemptPercents->avg() ?? 0), 2);

            $failedAttemptsCount = $attemptPercents
                ->filter(fn ($percent) => $percent < 70)
                ->count();

            $completedTestsCount = $attempts
                ->pluck('test_id')
                ->unique()
                ->count();

            $assignedTestsCount = $student->courses()
                ->withCount([
                    'tests as published_tests_count' => fn ($query) => $query
                        ->where('is_published', true)
                        ->where('is_archived', false),
                ])
                ->get()
                ->sum('published_tests_count');

            $completionPercent = $assignedTestsCount > 0
                ? round(($completedTestsCount / $assignedTestsCount) * 100, 2)
                : 0;

            $lastAttempt = $attempts->max('created_at');

            $daysSinceLastAttempt = $lastAttempt
                ? now()->diffInDays($lastAttempt)
                : 999;

            $supportTicketsCount = SupportTicket::query()
                ->where('user_id', $student->id)
                ->count();

            $firstPartAverage = round((float) ($attemptPercents->take(3)->avg() ?? 0), 2);
            $lastPartAverage = round((float) ($attemptPercents->reverse()->take(3)->avg() ?? 0), 2);

            $scoreTrend = $attemptsCount >= 2
                ? round($lastPartAverage - $firstPartAverage, 2)
                : 0;

            return [
                'id' => $student->id,
                'name' => $student->name,
                'group_name' => $student->classGroup?->name ?? 'Без группы',

                'average_score' => $averageScore,
                'average_percent' => $averagePercent,
                'best_score' => $bestScore,
                'attempts_count' => $attemptsCount,
                'failed_attempts_count' => $failedAttemptsCount,

                'completed_tests_count' => $completedTestsCount,
                'assigned_tests_count' => $assignedTestsCount,
                'completion_percent' => $completionPercent,

                'days_since_last_attempt' => $daysSinceLastAttempt,
                'support_tickets_count' => $supportTicketsCount,
                'score_trend' => $scoreTrend,
            ];
        })->values()->toArray();
    }

    private function buildTestsData(User $user): array
    {
        $testsQuery = Test::query()->with(['course', 'attempts']);

        if ($user->role === 'teacher') {
            $testsQuery->whereHas('course', function ($query) use ($user) {
                $query->where('teacher_id', $user->id);
            });
        }

        return $testsQuery->get()->map(function (Test $test) {
            $attempts = $test->attempts;
            $attemptsCount = $attempts->count();

            $percents = $attempts->map(function (TestAttempt $attempt) {
                if ($attempt->max_score <= 0) {
                    return 0;
                }

                return round(($attempt->score / $attempt->max_score) * 100, 2);
            });

            $averagePercent = round((float) ($percents->avg() ?? 0), 2);

            $failedPercent = $attemptsCount > 0
                ? round(($percents->filter(fn ($percent) => $percent < 70)->count() / $attemptsCount) * 100, 2)
                : 0;

            return [
                'id' => $test->id,
                'title' => $test->title,
                'course_title' => $test->course?->title ?? 'Без курса',
                'attempts_count' => $attemptsCount,
                'average_percent' => $averagePercent,
                'failed_percent' => $failedPercent,
            ];
        })->values()->toArray();
    }

    private function mapStudentAnalytics(array $students): Collection
    {
        $studentIds = collect($students)->pluck('id')->all();

        $models = User::query()
            ->with('classGroup')
            ->whereIn('id', $studentIds)
            ->get()
            ->keyBy('id');

        return collect($students)->map(function (array $item) use ($models) {
            $item['student'] = $models->get($item['id']);

            return $item;
        });
    }

    private function mapTestAnalytics(array $tests): Collection
    {
        $testIds = collect($tests)->pluck('test.id')->filter()->all();

        $models = Test::query()
            ->with('course')
            ->whereIn('id', $testIds)
            ->get()
            ->keyBy('id');

        return collect($tests)->map(function (array $item) use ($models) {
            $testId = data_get($item, 'test.id');

            $item['test'] = $models->get($testId);

            return $item;
        });
    }
}
