<?php

namespace App\Http\Controllers;

use App\Models\ActionLog;
use App\Models\ClassGroup;
use App\Models\Course;
use App\Models\InvitationCode;
use App\Models\Material;
use App\Models\ScheduleEvent;
use App\Models\SupportTicket;
use App\Models\Test;
use App\Models\TestAttempt;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function admin()
    {
        $usersCount = User::count();
        $studentsCount = User::where('role', 'student')->count();
        $teachersCount = User::where('role', 'teacher')->count();
        $adminsCount = User::where('role', 'admin')->count();
        $pendingUsersCount = User::whereNull('role')->count();

        return view('dashboard.admin', [
            'usersCount' => $usersCount,
            'pendingUsersCount' => $pendingUsersCount,
            'pendingUsers' => User::whereNull('role')->latest()->limit(5)->get(),
            'studentsCount' => $studentsCount,
            'teachersCount' => $teachersCount,
            'adminsCount' => $adminsCount,

            'coursesCount' => Course::count(),
            'materialsCount' => Material::count(),
            'testsCount' => Test::count(),
            'attemptsCount' => TestAttempt::onlyStudentAttempts()->count(),
            'averageScore' => round(TestAttempt::onlyStudentAttempts()->avg('score') ?? 0, 2),

            'classGroupsCount' => ClassGroup::count(),
            'scheduleEventsCount' => ScheduleEvent::count(),
            'todayEventsCount' => ScheduleEvent::whereDate('starts_at', today())->count(),
            'openTicketsCount' => SupportTicket::whereIn('status', ['open', 'in_progress'])->count(),
            'activeInvitationCodesCount' => InvitationCode::where('is_active', true)->count(),

            'latestAttempts' => TestAttempt::with(['user', 'test'])->onlyStudentAttempts()->latest()->limit(5)->get(),
            'latestLogs' => ActionLog::with('user')->latest()->limit(5)->get(),
            'latestUsers' => User::latest()->limit(5)->get(),
            'latestTickets' => SupportTicket::with(['user', 'course', 'assignedTeacher'])->latest()->limit(5)->get(),
            'upcomingEvents' => ScheduleEvent::with(['course', 'teacher', 'classGroup'])
                ->where('starts_at', '>=', now())
                ->orderBy('starts_at')
                ->limit(3)
                ->get(),

            'studentsPercent' => $usersCount > 0 ? round(($studentsCount / $usersCount) * 100) : 0,
            'teachersPercent' => $usersCount > 0 ? round(($teachersCount / $usersCount) * 100) : 0,
            'adminsPercent' => $usersCount > 0 ? round(($adminsCount / $usersCount) * 100) : 0,
            'pendingPercent' => $usersCount > 0 ? round(($pendingUsersCount / $usersCount) * 100) : 0,
        ]);
    }

    public function teacher()
    {
        $teacherId = Auth::id();

        $courseIds = Course::where('teacher_id', $teacherId)->pluck('id');

        $testIds = Test::whereIn('course_id', $courseIds)->pluck('id');

        $publishedTestIds = Test::whereIn('course_id', $courseIds)
            ->where('is_published', true)
            ->where('is_archived', false)
            ->pluck('id');

        $studentInsights = $this->getTeacherStudentInsights($courseIds);

        $teacherCourses = Course::withCount([
                'students',
                'materials',
                'tests',
                'tests as published_tests_count' => fn ($query) => $query
                    ->where('is_published', true)
                    ->where('is_archived', false),
            ])
            ->where('teacher_id', $teacherId)
            ->latest()
            ->limit(4)
            ->get();

        $assignedStudentsCount = DB::table('course_user')
            ->whereIn('course_id', $courseIds)
            ->distinct('user_id')
            ->count('user_id');

        $attemptsCount = TestAttempt::whereIn('test_id', $testIds)
            ->onlyStudentAttempts()
            ->count();

        $averageScore = round(
            TestAttempt::whereIn('test_id', $testIds)->onlyStudentAttempts()->avg('score') ?? 0,
            2
        );

        $activeStudentsCount = $studentInsights
            ->filter(fn (array $student) => $student['attempts_count'] > 0)
            ->count();

        $overallProgress = $studentInsights->isNotEmpty()
            ? (int) round($studentInsights->avg('progress_percent'))
            : 0;

        return view('dashboard.teacher', [
            'coursesCount' => $courseIds->count(),
            'materialsCount' => Material::whereIn('course_id', $courseIds)->count(),
            'testsCount' => $testIds->count(),
            'publishedTestsCount' => $publishedTestIds->count(),
            'attemptsCount' => $attemptsCount,
            'averageScore' => $averageScore,

            'assignedStudentsCount' => $assignedStudentsCount,
            'activeStudentsCount' => $activeStudentsCount,
            'overallProgress' => $overallProgress,

            'teacherCourses' => $teacherCourses,

            'latestAttempts' => TestAttempt::with(['user', 'test.course'])
                ->whereIn('test_id', $testIds)
                ->onlyStudentAttempts()
                ->latest()
                ->limit(5)
                ->get(),

            'laggingStudents' => $studentInsights
                ->filter(fn (array $student) => $student['assigned_tests_count'] > 0
                    && $student['completed_tests_count'] > 0
                    && $student['completed_tests_count'] < $student['assigned_tests_count'])
                ->sortBy('progress_percent')
                ->take(5)
                ->values(),

            'activeStudents' => $studentInsights
                ->filter(fn (array $student) => $student['attempts_count'] > 0)
                ->sortByDesc('attempts_count')
                ->take(5)
                ->values(),

            'studentsWithoutAttempts' => $studentInsights
                ->filter(fn (array $student) => $student['assigned_tests_count'] > 0 && $student['attempts_count'] === 0)
                ->sortByDesc('assigned_tests_count')
                ->take(5)
                ->values(),

            'upcomingEvents' => ScheduleEvent::with(['course', 'classGroup'])
                ->where('teacher_id', $teacherId)
                ->where('starts_at', '>=', now())
                ->orderBy('starts_at')
                ->limit(3)
                ->get(),

            'openTicketsCount' => SupportTicket::where(function ($query) use ($teacherId, $courseIds) {
                    $query->where('assigned_teacher_id', $teacherId)
                        ->orWhereIn('course_id', $courseIds);
                })
                ->whereIn('status', ['open', 'in_progress'])
                ->count(),

            'latestTickets' => SupportTicket::with(['user', 'course'])
                ->where(function ($query) use ($teacherId, $courseIds) {
                    $query->where('assigned_teacher_id', $teacherId)
                        ->orWhereIn('course_id', $courseIds);
                })
                ->latest()
                ->limit(4)
                ->get(),
        ]);
    }

    public function student()
    {
        $user = Auth::user();

        $courseIds = $user->courses()->pluck('courses.id');

        $availableTests = Test::with('course')
            ->whereIn('course_id', $courseIds)
            ->where('is_published', true)
            ->where('is_archived', false)
            ->latest()
            ->get();

        $attempts = TestAttempt::with(['test.course'])
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        $availableTestsCount = $availableTests->count();
        $attemptsCount = $attempts->count();

        $averageScore = round((float) ($attempts->avg('score') ?? 0), 2);
        $bestScore = round((float) ($attempts->max('score') ?? 0), 2);

        $completedTestsCount = $attempts
            ->pluck('test_id')
            ->unique()
            ->count();

        $progressPercent = $availableTestsCount > 0
            ? (int) round((min($completedTestsCount, $availableTestsCount) / $availableTestsCount) * 100)
            : 0;

        $latestAttempts = $attempts->take(5)->values();
        $latestTests = $availableTests->take(5)->values();

        $testAttemptCounts = $attempts
            ->groupBy('test_id')
            ->map(fn (Collection $items) => $items->count())
            ->toArray();

        $bestPercentByTest = $attempts
            ->groupBy('test_id')
            ->map(function (Collection $items) {
                return $items->max(function (TestAttempt $attempt) {
                    return $attempt->max_score > 0
                        ? round(($attempt->score / $attempt->max_score) * 100)
                        : 0;
                });
            })
            ->toArray();

        $upcomingEvents = collect();

        if ($user->class_group_id) {
            $upcomingEvents = ScheduleEvent::with(['course', 'teacher'])
                ->where('class_group_id', $user->class_group_id)
                ->where('starts_at', '>=', now())
                ->orderBy('starts_at')
                ->limit(3)
                ->get();
        }

        return view('dashboard.student', [
            'availableTestsCount' => $availableTestsCount,
            'attemptsCount' => $attemptsCount,
            'averageScore' => $averageScore,
            'bestScore' => $bestScore,
            'completedTestsCount' => $completedTestsCount,
            'progressPercent' => $progressPercent,
            'enrolledCoursesCount' => $courseIds->count(),
            'latestAttempts' => $latestAttempts,
            'latestTests' => $latestTests,
            'testAttemptCounts' => $testAttemptCounts,
            'bestPercentByTest' => $bestPercentByTest,
            'upcomingEvents' => $upcomingEvents,
            'openTicketsCount' => SupportTicket::where('user_id', $user->id)
                ->whereIn('status', ['open', 'in_progress'])
                ->count(),
            'currentStreak' => $this->getCurrentStreak($attempts),
        ]);
    }

    private function getTeacherStudentInsights(Collection $courseIds): Collection
    {
        if ($courseIds->isEmpty()) {
            return collect();
        }

        $students = User::query()
            ->select([
                'users.id',
                'users.name',
                DB::raw('COUNT(DISTINCT course_user.course_id) as courses_count'),
                DB::raw('COUNT(DISTINCT tests.id) as assigned_tests_count'),
            ])
            ->join('course_user', 'course_user.user_id', '=', 'users.id')
            ->leftJoin('tests', function ($join) {
                $join->on('tests.course_id', '=', 'course_user.course_id')
                    ->where('tests.is_published', true)
                    ->where('tests.is_archived', false);
            })
            ->where('users.role', 'student')
            ->whereIn('course_user.course_id', $courseIds)
            ->groupBy('users.id', 'users.name')
            ->orderBy('users.name')
            ->get();

        $attemptStats = TestAttempt::query()
            ->selectRaw('test_attempts.user_id, COUNT(*) as attempts_count')
            ->selectRaw('COUNT(DISTINCT test_attempts.test_id) as completed_tests_count')
            ->selectRaw('MAX(test_attempts.created_at) as last_attempt_at')
            ->join('tests', 'tests.id', '=', 'test_attempts.test_id')
            ->join('users', 'users.id', '=', 'test_attempts.user_id')
            ->where('users.role', 'student')
            ->whereIn('tests.course_id', $courseIds)
            ->where('tests.is_published', true)
            ->where('tests.is_archived', false)
            ->groupBy('test_attempts.user_id')
            ->get()
            ->keyBy('user_id');

        return $students->map(function ($student) use ($attemptStats) {
            $studentAttemptStats = $attemptStats->get($student->id);
            $assignedTestsCount = (int) $student->assigned_tests_count;
            $completedTestsCount = min($assignedTestsCount, (int) ($studentAttemptStats->completed_tests_count ?? 0));

            $lastAttemptAt = isset($studentAttemptStats->last_attempt_at)
                ? Carbon::parse($studentAttemptStats->last_attempt_at)
                : null;

            return [
                'id' => $student->id,
                'name' => $student->name,
                'courses_count' => (int) $student->courses_count,
                'assigned_tests_count' => $assignedTestsCount,
                'attempts_count' => (int) ($studentAttemptStats->attempts_count ?? 0),
                'completed_tests_count' => $completedTestsCount,
                'progress_percent' => $assignedTestsCount > 0
                    ? (int) round(($completedTestsCount / $assignedTestsCount) * 100)
                    : 0,
                'last_attempt_at' => $lastAttemptAt,
                'last_attempt_timestamp' => $lastAttemptAt?->timestamp ?? 0,
            ];
        });
    }

    private function getCurrentStreak(Collection $attempts): int
    {
        $dates = $attempts
            ->map(fn (TestAttempt $attempt) => $attempt->created_at->toDateString())
            ->unique()
            ->values();

        if ($dates->isEmpty()) {
            return 0;
        }

        $streak = 0;
        $cursor = now()->startOfDay();

        if (!$dates->contains($cursor->toDateString())) {
            $cursor->subDay();
        }

        while ($dates->contains($cursor->toDateString())) {
            $streak++;
            $cursor->subDay();
        }

        return $streak;
    }
}
