<?php

namespace App\Http\Controllers;

use App\Helpers\ActionLogger;
use App\Models\ActionLog;
use App\Models\ClassGroup;
use App\Models\Course;
use App\Models\InvitationCode;
use App\Models\Material;
use App\Models\Notification;
use App\Models\ScheduleEvent;
use App\Models\StudentAnswer;
use App\Models\SupportTicket;
use App\Models\Test;
use App\Models\TestAttempt;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

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

    public function digitalCurator()
    {
        $user = Auth::user();
        $learningGoal = $user->learning_goal ?: 'score_80';

        $courses = $user->courses()
            ->with('teacher')
            ->withCount([
                'materials as published_materials_count' => fn ($query) => $query->where('is_published', true),
                'tests as published_tests_count' => fn ($query) => $query
                    ->where('is_published', true)
                    ->where('is_archived', false),
            ])
            ->get();

        $courseIds = $courses->pluck('id');

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

        $attemptsCount = $attempts->count();
        $attemptPercents = $attempts->map(fn (TestAttempt $attempt) => $attempt->max_score > 0
            ? round(($attempt->score / $attempt->max_score) * 100, 2)
            : 0);

        $availableTestsCount = $availableTests->count();
        $completedTestIds = $attempts->pluck('test_id')->unique()->values();
        $completedTestsCount = min($completedTestIds->count(), $availableTestsCount);
        $progressPercent = $availableTestsCount > 0
            ? (int) round(($completedTestsCount / $availableTestsCount) * 100)
            : 0;

        $averagePercent = round((float) ($attemptPercents->avg() ?? 0), 2);
        $bestPercent = round((float) ($attemptPercents->max() ?? 0), 2);
        $failedAttemptsCount = $attemptPercents->filter(fn ($percent) => $percent < 70)->count();
        $lastAttemptAt = $attempts->first()?->created_at;
        $daysSinceLastAttempt = $lastAttemptAt
            ? (int) floor(abs($lastAttemptAt->diffInDays(now())))
            : null;
        $currentStreak = $this->getCurrentStreak($attempts);

        $bestPercentByTest = $attempts
            ->groupBy('test_id')
            ->map(fn (Collection $items) => $items->max(fn (TestAttempt $attempt) => $attempt->max_score > 0
                ? round(($attempt->score / $attempt->max_score) * 100)
                : 0));

        $testAttemptCounts = $attempts
            ->groupBy('test_id')
            ->map(fn (Collection $items) => $items->count());

        $recommendedTests = $availableTests
            ->map(function (Test $test) use ($bestPercentByTest, $testAttemptCounts, $learningGoal) {
                $bestPercent = $bestPercentByTest->get($test->id);
                $usedAttempts = (int) $testAttemptCounts->get($test->id, 0);

                if ($bestPercent === null) {
                    $priorityScore = 100;
                    $status = 'Начать';
                    $hint = 'Тест ещё не пройден';
                } elseif ($bestPercent < 70) {
                    $priorityScore = 90;
                    $status = 'Повторить';
                    $hint = 'Результат ниже целевого уровня';
                } elseif ($bestPercent < 85) {
                    $priorityScore = 60;
                    $status = 'Улучшить';
                    $hint = 'Есть запас для роста';
                } else {
                    $priorityScore = 20;
                    $status = 'Закрепить';
                    $hint = 'Можно пройти для поддержания темпа';
                }

                if ($learningGoal === 'score_80' && ($bestPercent === null || $bestPercent < 80)) {
                    $priorityScore += 18;
                    $hint .= '. Цель: выйти на 80%+';
                }

                if ($learningGoal === 'complete_tests' && $bestPercent === null) {
                    $priorityScore += 25;
                    $hint .= '. Цель: закрыть все тесты';
                }

                if ($learningGoal === 'exam_prep' && ($bestPercent === null || $bestPercent < 85)) {
                    $priorityScore += 20;
                    $hint .= '. Цель: подготовка к экзамену';
                }

                return [
                    'test' => $test,
                    'best_percent' => $bestPercent,
                    'used_attempts' => $usedAttempts,
                    'priority_score' => $priorityScore,
                    'status' => $status,
                    'hint' => $hint,
                ];
            })
            ->sortByDesc('priority_score')
            ->take(4)
            ->values();

        $weakCourseIds = $recommendedTests
            ->filter(fn (array $item) => $item['priority_score'] >= 60)
            ->pluck('test.course_id')
            ->filter()
            ->unique();

        $recommendedMaterials = Material::with('course')
            ->whereIn('course_id', $courseIds)
            ->where('is_published', true)
            ->latest()
            ->limit(8)
            ->get()
            ->sortByDesc(fn (Material $material) => $weakCourseIds->contains($material->course_id) ? 1 : 0)
            ->take(4)
            ->values();

        $courseProgress = $courses
            ->map(function (Course $course) use ($availableTests, $completedTestIds, $attempts) {
                $courseTests = $availableTests->where('course_id', $course->id);
                $courseTestIds = $courseTests->pluck('id');
                $completedCount = $completedTestIds->intersect($courseTestIds)->count();
                $testsCount = $courseTests->count();

                $coursePercents = $attempts
                    ->filter(fn (TestAttempt $attempt) => $attempt->test?->course_id === $course->id)
                    ->map(fn (TestAttempt $attempt) => $attempt->max_score > 0
                        ? round(($attempt->score / $attempt->max_score) * 100, 2)
                        : 0);

                return [
                    'course' => $course,
                    'tests_count' => $testsCount,
                    'completed_count' => $completedCount,
                    'progress_percent' => $testsCount > 0 ? (int) round(($completedCount / $testsCount) * 100) : 0,
                    'average_percent' => round((float) ($coursePercents->avg() ?? 0), 2),
                ];
            })
            ->sortBy('progress_percent')
            ->values();

        $openTicketsCount = SupportTicket::where('user_id', $user->id)
            ->whereIn('status', ['new', 'open', 'in_progress'])
            ->count();

        $weakZones = $this->getWeakZones($user, $courseIds);
        $paceIndicator = $this->getPaceIndicator($courses, $availableTestsCount, $progressPercent, $daysSinceLastAttempt, $learningGoal);
        $improvementHistory = $this->getImprovementHistory($attempts, $completedTestIds, $currentStreak);

        $upcomingEvents = collect();

        if ($user->class_group_id) {
            $upcomingEvents = ScheduleEvent::with(['course', 'teacher'])
                ->where('class_group_id', $user->class_group_id)
                ->where('starts_at', '>=', now())
                ->orderBy('starts_at')
                ->limit(3)
                ->get();
        }

        $riskScore = $this->getStudentRiskScore(
            $availableTestsCount,
            $progressPercent,
            $averagePercent,
            $failedAttemptsCount,
            $daysSinceLastAttempt,
            $currentStreak
        );

        $riskProfile = $this->getStudentRiskProfile($riskScore);
        $priorityActions = $this->getCuratorPriorityActions(
            $courseIds,
            $availableTestsCount,
            $progressPercent,
            $averagePercent,
            $daysSinceLastAttempt,
            $currentStreak,
            $openTicketsCount,
            $recommendedTests,
            $learningGoal,
            $weakZones
        );

        $nextBestStep = $this->getNextBestStep($priorityActions, $recommendedMaterials, $learningGoal);
        $competencyMap = $this->getCompetencyMap($user, $courseIds);
        $riskFactors = $this->getCuratorRiskFactors(
            $availableTestsCount,
            $progressPercent,
            $averagePercent,
            $failedAttemptsCount,
            $daysSinceLastAttempt,
            $currentStreak
        );
        $engagementScore = $this->getEngagementScore(
            $attemptsCount,
            $progressPercent,
            $daysSinceLastAttempt,
            $currentStreak,
            $openTicketsCount
        );
        $behaviorScenario = $this->getBehaviorScenario($attemptsCount, $averagePercent, $engagementScore, $riskScore);
        $interventionSignals = $this->getInterventionSignals(
            $riskScore,
            $averagePercent,
            $failedAttemptsCount,
            $daysSinceLastAttempt,
            $paceIndicator,
            $weakZones
        );
        $weeklyPlan = $this->getWeeklyPlan(
            $nextBestStep,
            $priorityActions,
            $recommendedMaterials,
            $upcomingEvents,
            $weakZones
        );
        $riskForecast = $this->getRiskForecast($riskScore, $progressPercent, $averagePercent, $daysSinceLastAttempt);
        $digitalProfile = $this->getDigitalProfile(
            $learningGoal,
            $engagementScore,
            $behaviorScenario,
            $paceIndicator,
            $competencyMap,
            $interventionSignals
        );

        return view('digital-curator.index', [
            'learningGoal' => $learningGoal,
            'learningGoalOptions' => $this->learningGoalOptions(),
            'coursesCount' => $courses->count(),
            'availableTestsCount' => $availableTestsCount,
            'completedTestsCount' => $completedTestsCount,
            'progressPercent' => $progressPercent,
            'averagePercent' => $averagePercent,
            'bestPercent' => $bestPercent,
            'failedAttemptsCount' => $failedAttemptsCount,
            'daysSinceLastAttempt' => $daysSinceLastAttempt,
            'currentStreak' => $currentStreak,
            'openTicketsCount' => $openTicketsCount,
            'riskScore' => $riskScore,
            'riskProfile' => $riskProfile,
            'paceIndicator' => $paceIndicator,
            'nextBestStep' => $nextBestStep,
            'priorityActions' => $priorityActions,
            'weakZones' => $weakZones,
            'improvementHistory' => $improvementHistory,
            'recommendedTests' => $recommendedTests,
            'recommendedMaterials' => $recommendedMaterials,
            'courseProgress' => $courseProgress,
            'upcomingEvents' => $upcomingEvents,
            'digitalProfile' => $digitalProfile,
            'riskFactors' => $riskFactors,
            'competencyMap' => $competencyMap,
            'weeklyPlan' => $weeklyPlan,
            'interventionSignals' => $interventionSignals,
            'behaviorScenario' => $behaviorScenario,
            'engagementScore' => $engagementScore,
            'riskForecast' => $riskForecast,
        ]);
    }

    public function teacherDigitalCurator(Request $request)
    {
        $teacher = Auth::user();
        $isAdmin = $teacher->role === 'admin';
        $teacherCourseIds = $isAdmin
            ? Course::pluck('id')
            : Course::where('teacher_id', $teacher->id)->pluck('id');

        $studentIds = $isAdmin
            ? User::where('role', 'student')->pluck('id')
            : DB::table('course_user')
                ->whereIn('course_id', $teacherCourseIds)
                ->pluck('user_id')
                ->unique();

        $students = User::query()
            ->with('classGroup')
            ->where('role', 'student')
            ->whereIn('id', $studentIds)
            ->orderBy('name')
            ->get();

        $selectedStudent = $students->firstWhere('id', (int) $request->integer('student_id'))
            ?: $students->first();

        if (!$selectedStudent) {
            return view('digital-curator.teacher', [
                'students' => $students,
                'selectedStudent' => null,
                'learningGoalOptions' => $this->learningGoalOptions(),
                'isAdminCurator' => $isAdmin,
            ]);
        }

        $studentCourses = $selectedStudent->courses()
            ->whereIn('courses.id', $teacherCourseIds)
            ->with('teacher')
            ->withCount([
                'materials as published_materials_count' => fn ($query) => $query->where('is_published', true),
                'tests as published_tests_count' => fn ($query) => $query
                    ->where('is_published', true)
                    ->where('is_archived', false),
            ])
            ->get();

        $curatorData = $this->buildCuratorData($selectedStudent, $studentCourses);

        return view('digital-curator.teacher', array_merge($curatorData, [
            'students' => $students,
            'selectedStudent' => $selectedStudent,
            'learningGoalOptions' => $this->learningGoalOptions(),
            'isAdminCurator' => $isAdmin,
        ]));
    }

    public function updateLearningGoal(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'learning_goal' => ['required', Rule::in(array_keys($this->learningGoalOptions()))],
        ]);

        $user = Auth::user();

        $user->update([
            'learning_goal' => $validated['learning_goal'],
        ]);

        return redirect()
            ->route('digital-curator.index')
            ->with('success', 'Цель обучения обновлена');
    }

    public function requestCuratorHelp(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $course = $user->courses()
            ->with('teacher')
            ->orderBy('title')
            ->first();

        if (!$course) {
            return redirect()
                ->route('digital-curator.index')
                ->with('error', 'Сначала нужно быть записанным хотя бы на один курс.');
        }

        $existingTicket = SupportTicket::query()
            ->where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->where('subject', 'Помощь цифрового куратора')
            ->whereIn('status', [SupportTicket::STATUS_NEW, SupportTicket::STATUS_IN_PROGRESS])
            ->latest()
            ->first();

        if ($existingTicket) {
            return redirect()
                ->route('support-tickets.show', $existingTicket)
                ->with('success', 'У тебя уже есть открытая заявка к преподавателю.');
        }

        $ticket = SupportTicket::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'assigned_teacher_id' => $course->teacher_id,
            'type' => SupportTicket::TYPE_TEACHER_REQUEST,
            'subject' => 'Помощь цифрового куратора',
            'message' => $request->input('message', 'Цифровой куратор обнаружил высокий риск отставания. Нужна консультация преподавателя по учебному плану и слабым темам.'),
            'status' => SupportTicket::STATUS_NEW,
        ]);

        if ($ticket->assigned_teacher_id) {
            Notification::create([
                'user_id' => $ticket->assigned_teacher_id,
                'title' => 'Запрос помощи от цифрового куратора',
                'body' => 'Студент ' . $user->name . ' запросил помощь по курсу: ' . $course->title,
                'type' => 'system',
                'action_url' => route('support-tickets.show', $ticket),
                'is_read' => false,
            ]);
        }

        ActionLogger::log(
            'Заявка цифрового куратора',
            'Создана автоматическая заявка преподавателю: ' . $course->title,
            $request
        );

        return redirect()
            ->route('support-tickets.show', $ticket)
            ->with('success', 'Заявка преподавателю создана');
    }

    private function learningGoalOptions(): array
    {
        return [
            'score_80' => [
                'label' => 'Получить 80%+',
                'description' => 'Куратор будет чаще поднимать тесты, где результат ниже 80%.',
            ],
            'complete_tests' => [
                'label' => 'Закрыть все тесты',
                'description' => 'Главный приоритет получат непройденные тесты.',
            ],
            'exam_prep' => [
                'label' => 'Подготовиться к экзамену',
                'description' => 'Куратор усилит повторение слабых тем и тестов ниже 85%.',
            ],
        ];
    }

    private function buildCuratorData(User $user, Collection $courses): array
    {
        $learningGoal = $user->learning_goal ?: 'score_80';
        $courseIds = $courses->pluck('id');

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

        $attemptsCount = $attempts->count();
        $attemptPercents = $attempts->map(fn (TestAttempt $attempt) => $attempt->max_score > 0
            ? round(($attempt->score / $attempt->max_score) * 100, 2)
            : 0);

        $availableTestsCount = $availableTests->count();
        $completedTestIds = $attempts->pluck('test_id')->unique()->values();
        $completedTestsCount = min($completedTestIds->count(), $availableTestsCount);
        $progressPercent = $availableTestsCount > 0
            ? (int) round(($completedTestsCount / $availableTestsCount) * 100)
            : 0;

        $averagePercent = round((float) ($attemptPercents->avg() ?? 0), 2);
        $bestPercent = round((float) ($attemptPercents->max() ?? 0), 2);
        $failedAttemptsCount = $attemptPercents->filter(fn ($percent) => $percent < 70)->count();
        $lastAttemptAt = $attempts->first()?->created_at;
        $daysSinceLastAttempt = $lastAttemptAt
            ? (int) floor(abs($lastAttemptAt->diffInDays(now())))
            : null;
        $currentStreak = $this->getCurrentStreak($attempts);

        $bestPercentByTest = $attempts
            ->groupBy('test_id')
            ->map(fn (Collection $items) => $items->max(fn (TestAttempt $attempt) => $attempt->max_score > 0
                ? round(($attempt->score / $attempt->max_score) * 100)
                : 0));

        $testAttemptCounts = $attempts
            ->groupBy('test_id')
            ->map(fn (Collection $items) => $items->count());

        $recommendedTests = $availableTests
            ->map(function (Test $test) use ($bestPercentByTest, $testAttemptCounts, $learningGoal) {
                $bestPercent = $bestPercentByTest->get($test->id);
                $usedAttempts = (int) $testAttemptCounts->get($test->id, 0);

                if ($bestPercent === null) {
                    $priorityScore = 100;
                    $status = 'Начать';
                    $hint = 'Тест ещё не пройден';
                } elseif ($bestPercent < 70) {
                    $priorityScore = 90;
                    $status = 'Повторить';
                    $hint = 'Результат ниже целевого уровня';
                } elseif ($bestPercent < 85) {
                    $priorityScore = 60;
                    $status = 'Улучшить';
                    $hint = 'Есть запас для роста';
                } else {
                    $priorityScore = 20;
                    $status = 'Закрепить';
                    $hint = 'Можно пройти для поддержания темпа';
                }

                if ($learningGoal === 'score_80' && ($bestPercent === null || $bestPercent < 80)) {
                    $priorityScore += 18;
                    $hint .= '. Цель: выйти на 80%+';
                }

                if ($learningGoal === 'complete_tests' && $bestPercent === null) {
                    $priorityScore += 25;
                    $hint .= '. Цель: закрыть все тесты';
                }

                if ($learningGoal === 'exam_prep' && ($bestPercent === null || $bestPercent < 85)) {
                    $priorityScore += 20;
                    $hint .= '. Цель: подготовка к экзамену';
                }

                return [
                    'test' => $test,
                    'best_percent' => $bestPercent,
                    'used_attempts' => $usedAttempts,
                    'priority_score' => $priorityScore,
                    'status' => $status,
                    'hint' => $hint,
                ];
            })
            ->sortByDesc('priority_score')
            ->take(4)
            ->values();

        $weakCourseIds = $recommendedTests
            ->filter(fn (array $item) => $item['priority_score'] >= 60)
            ->pluck('test.course_id')
            ->filter()
            ->unique();

        $recommendedMaterials = Material::with('course')
            ->whereIn('course_id', $courseIds)
            ->where('is_published', true)
            ->latest()
            ->limit(8)
            ->get()
            ->sortByDesc(fn (Material $material) => $weakCourseIds->contains($material->course_id) ? 1 : 0)
            ->take(4)
            ->values();

        $courseProgress = $courses
            ->map(function (Course $course) use ($availableTests, $completedTestIds, $attempts) {
                $courseTests = $availableTests->where('course_id', $course->id);
                $courseTestIds = $courseTests->pluck('id');
                $completedCount = $completedTestIds->intersect($courseTestIds)->count();
                $testsCount = $courseTests->count();

                $coursePercents = $attempts
                    ->filter(fn (TestAttempt $attempt) => $attempt->test?->course_id === $course->id)
                    ->map(fn (TestAttempt $attempt) => $attempt->max_score > 0
                        ? round(($attempt->score / $attempt->max_score) * 100, 2)
                        : 0);

                return [
                    'course' => $course,
                    'tests_count' => $testsCount,
                    'completed_count' => $completedCount,
                    'progress_percent' => $testsCount > 0 ? (int) round(($completedCount / $testsCount) * 100) : 0,
                    'average_percent' => round((float) ($coursePercents->avg() ?? 0), 2),
                ];
            })
            ->sortBy('progress_percent')
            ->values();

        $openTicketsCount = SupportTicket::where('user_id', $user->id)
            ->whereIn('status', ['new', 'open', 'in_progress'])
            ->count();

        $weakZones = $this->getWeakZones($user, $courseIds);
        $paceIndicator = $this->getPaceIndicator($courses, $availableTestsCount, $progressPercent, $daysSinceLastAttempt, $learningGoal);
        $improvementHistory = $this->getImprovementHistory($attempts, $completedTestIds, $currentStreak);

        $riskScore = $this->getStudentRiskScore(
            $availableTestsCount,
            $progressPercent,
            $averagePercent,
            $failedAttemptsCount,
            $daysSinceLastAttempt,
            $currentStreak
        );

        $riskProfile = $this->getStudentRiskProfile($riskScore);
        $priorityActions = $this->getCuratorPriorityActions(
            $courseIds,
            $availableTestsCount,
            $progressPercent,
            $averagePercent,
            $daysSinceLastAttempt,
            $currentStreak,
            $openTicketsCount,
            $recommendedTests,
            $learningGoal,
            $weakZones
        );
        $nextBestStep = $this->getNextBestStep($priorityActions, $recommendedMaterials, $learningGoal);
        $competencyMap = $this->getCompetencyMap($user, $courseIds);
        $riskFactors = $this->getCuratorRiskFactors(
            $availableTestsCount,
            $progressPercent,
            $averagePercent,
            $failedAttemptsCount,
            $daysSinceLastAttempt,
            $currentStreak
        );
        $engagementScore = $this->getEngagementScore(
            $attemptsCount,
            $progressPercent,
            $daysSinceLastAttempt,
            $currentStreak,
            $openTicketsCount
        );
        $behaviorScenario = $this->getBehaviorScenario($attemptsCount, $averagePercent, $engagementScore, $riskScore);
        $interventionSignals = $this->getInterventionSignals(
            $riskScore,
            $averagePercent,
            $failedAttemptsCount,
            $daysSinceLastAttempt,
            $paceIndicator,
            $weakZones
        );
        $weeklyPlan = $this->getWeeklyPlan(
            $nextBestStep,
            $priorityActions,
            $recommendedMaterials,
            collect(),
            $weakZones
        );
        $riskForecast = $this->getRiskForecast($riskScore, $progressPercent, $averagePercent, $daysSinceLastAttempt);
        $digitalProfile = $this->getDigitalProfile(
            $learningGoal,
            $engagementScore,
            $behaviorScenario,
            $paceIndicator,
            $competencyMap,
            $interventionSignals
        );

        return [
            'learningGoal' => $learningGoal,
            'coursesCount' => $courses->count(),
            'availableTestsCount' => $availableTestsCount,
            'completedTestsCount' => $completedTestsCount,
            'progressPercent' => $progressPercent,
            'averagePercent' => $averagePercent,
            'bestPercent' => $bestPercent,
            'failedAttemptsCount' => $failedAttemptsCount,
            'daysSinceLastAttempt' => $daysSinceLastAttempt,
            'currentStreak' => $currentStreak,
            'openTicketsCount' => $openTicketsCount,
            'riskScore' => $riskScore,
            'riskProfile' => $riskProfile,
            'paceIndicator' => $paceIndicator,
            'nextBestStep' => $nextBestStep,
            'priorityActions' => $priorityActions,
            'weakZones' => $weakZones,
            'improvementHistory' => $improvementHistory,
            'recommendedTests' => $recommendedTests,
            'recommendedMaterials' => $recommendedMaterials,
            'courseProgress' => $courseProgress,
            'digitalProfile' => $digitalProfile,
            'riskFactors' => $riskFactors,
            'competencyMap' => $competencyMap,
            'weeklyPlan' => $weeklyPlan,
            'interventionSignals' => $interventionSignals,
            'behaviorScenario' => $behaviorScenario,
            'engagementScore' => $engagementScore,
            'riskForecast' => $riskForecast,
        ];
    }

    private function getCompetencyMap(User $user, Collection $courseIds): Collection
    {
        if ($courseIds->isEmpty()) {
            return collect();
        }

        return StudentAnswer::query()
            ->with(['question.test.course', 'attempt'])
            ->whereHas('attempt', fn ($query) => $query->where('user_id', $user->id))
            ->whereHas('question.test', fn ($query) => $query->whereIn('course_id', $courseIds))
            ->latest()
            ->limit(300)
            ->get()
            ->filter(fn (StudentAnswer $answer) => $answer->question !== null)
            ->groupBy(function (StudentAnswer $answer) {
                return $answer->question?->topic
                    ?: $answer->question?->test?->title
                    ?: 'Тема не указана';
            })
            ->map(function (Collection $answers, string $topic) {
                $latestAnswer = $answers->sortByDesc('created_at')->first();
                $totalCount = $answers->count();
                $correctCount = $answers->where('is_correct', true)->count();
                $masteryPercent = $totalCount > 0
                    ? (int) round(($correctCount / $totalCount) * 100)
                    : 0;

                if ($masteryPercent >= 80) {
                    $status = 'Освоено';
                    $class = 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-300';
                    $bar = 'bg-emerald-500';
                } elseif ($masteryPercent >= 55) {
                    $status = 'Закрепить';
                    $class = 'bg-amber-50 text-amber-600 dark:bg-amber-500/15 dark:text-amber-300';
                    $bar = 'bg-amber-500';
                } else {
                    $status = 'Пробел';
                    $class = 'bg-red-50 text-red-600 dark:bg-red-500/15 dark:text-red-300';
                    $bar = 'bg-red-500';
                }

                return [
                    'topic' => $topic,
                    'course_title' => $latestAnswer?->question?->test?->course?->title ?? 'Без курса',
                    'test_title' => $latestAnswer?->question?->test?->title ?? 'Тест',
                    'mastery_percent' => $masteryPercent,
                    'correct_count' => $correctCount,
                    'wrong_count' => $totalCount - $correctCount,
                    'total_count' => $totalCount,
                    'status' => $status,
                    'class' => $class,
                    'bar' => $bar,
                ];
            })
            ->sortBy('mastery_percent')
            ->take(6)
            ->values();
    }

    private function getWeakZones(User $user, Collection $courseIds): Collection
    {
        if ($courseIds->isEmpty()) {
            return collect();
        }

        return StudentAnswer::query()
            ->with(['question.test.course', 'attempt'])
            ->where('is_correct', false)
            ->whereHas('attempt', fn ($query) => $query->where('user_id', $user->id))
            ->whereHas('question.test', fn ($query) => $query->whereIn('course_id', $courseIds))
            ->latest()
            ->limit(80)
            ->get()
            ->groupBy(function (StudentAnswer $answer) {
                return $answer->question?->topic
                    ?: $answer->question?->test?->title
                    ?: 'Тема не указана';
            })
            ->map(function (Collection $answers, string $topic) {
                $latestAnswer = $answers->sortByDesc('created_at')->first();

                return [
                    'topic' => $topic,
                    'wrong_count' => $answers->count(),
                    'course_title' => $latestAnswer?->question?->test?->course?->title ?? 'Без курса',
                    'test_title' => $latestAnswer?->question?->test?->title ?? 'Тест',
                    'last_seen_at' => $latestAnswer?->created_at,
                ];
            })
            ->sortByDesc('wrong_count')
            ->take(4)
            ->values();
    }

    private function getPaceIndicator(
        Collection $courses,
        int $availableTestsCount,
        int $progressPercent,
        ?int $daysSinceLastAttempt,
        string $learningGoal
    ): array {
        if ($availableTestsCount === 0 || $courses->isEmpty()) {
            return [
                'label' => 'Ожидает данных',
                'description' => 'Куратор оценит темп, когда появятся курсы и опубликованные тесты.',
                'class' => 'bg-slate-100 text-slate-600 dark:bg-white/5 dark:text-slate-300',
                'expected_percent' => 0,
            ];
        }

        $firstEnrollment = $courses
            ->map(fn (Course $course) => $course->pivot?->created_at)
            ->filter()
            ->sort()
            ->first();

        $daysInLearning = $firstEnrollment
            ? max(1, (int) floor(abs(Carbon::parse($firstEnrollment)->diffInDays(now()))))
            : 1;

        $goalMultiplier = match ($learningGoal) {
            'exam_prep' => 1.25,
            'complete_tests' => 1.1,
            default => 1,
        };

        $expectedPercent = min(100, (int) round(($daysInLearning / 21) * 100 * $goalMultiplier));
        $delta = $progressPercent - $expectedPercent;

        if ($delta >= 12) {
            return [
                'label' => 'Опережаешь график',
                'description' => 'Фактический прогресс выше ожидаемого темпа.',
                'class' => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-300',
                'expected_percent' => $expectedPercent,
            ];
        }

        if ($delta < -12 || ($daysSinceLastAttempt !== null && $daysSinceLastAttempt > 10)) {
            return [
                'label' => 'Есть отставание',
                'description' => 'Нужно закрыть ближайший тест или повторить материал, чтобы вернуться в график.',
                'class' => 'bg-red-50 text-red-600 dark:bg-red-500/15 dark:text-red-300',
                'expected_percent' => $expectedPercent,
            ];
        }

        return [
            'label' => 'Идёшь по плану',
            'description' => 'Темп соответствует текущему учебному графику.',
            'class' => 'bg-blue-50 text-blue-600 dark:bg-blue-500/15 dark:text-blue-300',
            'expected_percent' => $expectedPercent,
        ];
    }

    private function getImprovementHistory(Collection $attempts, Collection $completedTestIds, int $currentStreak): array
    {
        $lastWeekStart = now()->subDays(7);
        $previousWeekStart = now()->subDays(14);

        $lastWeekPercents = $attempts
            ->filter(fn (TestAttempt $attempt) => $attempt->created_at >= $lastWeekStart)
            ->map(fn (TestAttempt $attempt) => $attempt->max_score > 0 ? ($attempt->score / $attempt->max_score) * 100 : 0);

        $previousWeekPercents = $attempts
            ->filter(fn (TestAttempt $attempt) => $attempt->created_at < $lastWeekStart && $attempt->created_at >= $previousWeekStart)
            ->map(fn (TestAttempt $attempt) => $attempt->max_score > 0 ? ($attempt->score / $attempt->max_score) * 100 : 0);

        $scoreDelta = $lastWeekPercents->isNotEmpty() && $previousWeekPercents->isNotEmpty()
            ? round($lastWeekPercents->avg() - $previousWeekPercents->avg(), 1)
            : null;

        $completedThisWeek = $attempts
            ->filter(fn (TestAttempt $attempt) => $attempt->created_at >= $lastWeekStart)
            ->pluck('test_id')
            ->unique()
            ->intersect($completedTestIds)
            ->count();

        return [
            [
                'label' => 'Динамика результата',
                'value' => $scoreDelta === null ? 'нет данных' : (($scoreDelta >= 0 ? '+' : '') . $scoreDelta . '%'),
                'hint' => 'Сравнение последних 7 дней с предыдущей неделей',
            ],
            [
                'label' => 'Закрыто за неделю',
                'value' => $completedThisWeek,
                'hint' => 'Уникальные тесты с попытками за 7 дней',
            ],
            [
                'label' => 'Активность',
                'value' => $lastWeekPercents->count(),
                'hint' => 'Попытки за последние 7 дней',
            ],
            [
                'label' => 'Серия',
                'value' => $currentStreak . ' дн.',
                'hint' => 'Дни активности подряд',
            ],
        ];
    }

    private function getNextBestStep(Collection $priorityActions, Collection $recommendedMaterials, string $learningGoal): array
    {
        $action = $priorityActions->first();

        if ($action) {
            return [
                'title' => $action['title'],
                'description' => $action['description'],
                'route' => $action['route'],
                'label' => $action['label'],
            ];
        }

        $material = $recommendedMaterials->first();

        if ($material) {
            return [
                'title' => 'Повторить материал',
                'description' => 'Лучший следующий шаг для цели "' . $this->learningGoalOptions()[$learningGoal]['label'] . '": ' . $material->title . '.',
                'route' => route('materials.show', $material),
                'label' => 'Открыть материал',
            ];
        }

        return [
            'title' => 'Открыть курсы',
            'description' => 'Куратор ждёт новых тестов и материалов, чтобы построить следующий шаг.',
            'route' => route('courses.index'),
            'label' => 'К курсам',
        ];
    }

    private function getCuratorRiskFactors(
        int $availableTestsCount,
        int $progressPercent,
        float $averagePercent,
        int $failedAttemptsCount,
        ?int $daysSinceLastAttempt,
        int $currentStreak
    ): Collection {
        $factors = collect();

        if ($availableTestsCount === 0) {
            return collect([
                [
                    'label' => 'Недостаточно данных',
                    'value' => 'нет тестов',
                    'impact' => 15,
                    'description' => 'Куратор не может построить точный риск без опубликованных тестов.',
                    'class' => 'bg-slate-100 text-slate-600 dark:bg-white/5 dark:text-slate-300',
                ],
            ]);
        }

        if ($progressPercent < 40) {
            $factors->push([
                'label' => 'Низкий прогресс',
                'value' => $progressPercent . '%',
                'impact' => 30,
                'description' => 'Закрыта малая часть назначенных тестов.',
                'class' => 'bg-red-50 text-red-600 dark:bg-red-500/15 dark:text-red-300',
            ]);
        } elseif ($progressPercent < 70) {
            $factors->push([
                'label' => 'Неполный прогресс',
                'value' => $progressPercent . '%',
                'impact' => 15,
                'description' => 'Есть незавершённые тесты, которые снижают темп.',
                'class' => 'bg-amber-50 text-amber-600 dark:bg-amber-500/15 dark:text-amber-300',
            ]);
        }

        if ($averagePercent > 0 && $averagePercent < 60) {
            $factors->push([
                'label' => 'Низкий результат',
                'value' => $averagePercent . '%',
                'impact' => 30,
                'description' => 'Средний процент выполнения ниже устойчивого уровня.',
                'class' => 'bg-red-50 text-red-600 dark:bg-red-500/15 dark:text-red-300',
            ]);
        } elseif ($averagePercent > 0 && $averagePercent < 75) {
            $factors->push([
                'label' => 'Результат требует закрепления',
                'value' => $averagePercent . '%',
                'impact' => 15,
                'description' => 'Есть риск ошибок при переходе к более сложным темам.',
                'class' => 'bg-amber-50 text-amber-600 dark:bg-amber-500/15 dark:text-amber-300',
            ]);
        }

        if ($failedAttemptsCount >= 3) {
            $factors->push([
                'label' => 'Повторные неудачи',
                'value' => (string) $failedAttemptsCount,
                'impact' => 20,
                'description' => 'Несколько попыток ниже 70% указывают на устойчивый пробел.',
                'class' => 'bg-red-50 text-red-600 dark:bg-red-500/15 dark:text-red-300',
            ]);
        } elseif ($failedAttemptsCount > 0) {
            $factors->push([
                'label' => 'Есть ошибки',
                'value' => (string) $failedAttemptsCount,
                'impact' => 10,
                'description' => 'Часть попыток ниже целевого порога.',
                'class' => 'bg-orange-50 text-orange-600 dark:bg-orange-500/15 dark:text-orange-300',
            ]);
        }

        if ($daysSinceLastAttempt === null) {
            $factors->push([
                'label' => 'Нет учебной активности',
                'value' => 'нет попыток',
                'impact' => 20,
                'description' => 'Куратор пока не видит прохождений тестов.',
                'class' => 'bg-red-50 text-red-600 dark:bg-red-500/15 dark:text-red-300',
            ]);
        } elseif ($daysSinceLastAttempt > 14) {
            $factors->push([
                'label' => 'Долгая пауза',
                'value' => $daysSinceLastAttempt . ' дн.',
                'impact' => 25,
                'description' => 'Длительное отсутствие попыток повышает риск отставания.',
                'class' => 'bg-red-50 text-red-600 dark:bg-red-500/15 dark:text-red-300',
            ]);
        } elseif ($daysSinceLastAttempt > 7) {
            $factors->push([
                'label' => 'Пауза в обучении',
                'value' => $daysSinceLastAttempt . ' дн.',
                'impact' => 15,
                'description' => 'Стоит вернуться к коротким регулярным сессиям.',
                'class' => 'bg-amber-50 text-amber-600 dark:bg-amber-500/15 dark:text-amber-300',
            ]);
        }

        if ($currentStreak === 0) {
            $factors->push([
                'label' => 'Нет серии активности',
                'value' => '0 дн.',
                'impact' => 10,
                'description' => 'Регулярность пока не сформирована.',
                'class' => 'bg-slate-100 text-slate-600 dark:bg-white/5 dark:text-slate-300',
            ]);
        }

        if ($factors->isEmpty()) {
            $factors->push([
                'label' => 'Критичных факторов нет',
                'value' => 'норма',
                'impact' => 0,
                'description' => 'Показатели не выходят за риск-пороги куратора.',
                'class' => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-300',
            ]);
        }

        return $factors->values();
    }

    private function getEngagementScore(
        int $attemptsCount,
        int $progressPercent,
        ?int $daysSinceLastAttempt,
        int $currentStreak,
        int $openTicketsCount
    ): int {
        $attemptScore = min(35, $attemptsCount * 4);
        $progressScore = min(15, (int) round($progressPercent * 0.15));
        $streakScore = min(20, $currentStreak * 5);

        $recencyScore = match (true) {
            $daysSinceLastAttempt === null => 0,
            $daysSinceLastAttempt <= 3 => 30,
            $daysSinceLastAttempt <= 7 => 24,
            $daysSinceLastAttempt <= 14 => 14,
            $daysSinceLastAttempt <= 30 => 7,
            default => 0,
        };

        $supportPenalty = min(10, $openTicketsCount * 3);

        return max(0, min(100, $attemptScore + $progressScore + $streakScore + $recencyScore - $supportPenalty));
    }

    private function getBehaviorScenario(int $attemptsCount, float $averagePercent, int $engagementScore, int $riskScore): array
    {
        if ($attemptsCount === 0) {
            return [
                'label' => 'Старт без данных',
                'description' => 'Нужно пройти первый тест, чтобы куратор уточнил профиль.',
                'class' => 'bg-slate-100 text-slate-600 dark:bg-white/5 dark:text-slate-300',
            ];
        }

        if ($riskScore >= 65) {
            return [
                'label' => 'Зона педагогической поддержки',
                'description' => 'Нужны короткий план, повторение слабых тем и обратная связь преподавателя.',
                'class' => 'bg-red-50 text-red-600 dark:bg-red-500/15 dark:text-red-300',
            ];
        }

        if ($engagementScore >= 60 && $averagePercent < 70) {
            return [
                'label' => 'Активный, но с трудностями',
                'description' => 'Студент работает регулярно, но результат требует разбора ошибок.',
                'class' => 'bg-orange-50 text-orange-600 dark:bg-orange-500/15 dark:text-orange-300',
            ];
        }

        if ($engagementScore < 45 && $averagePercent >= 75) {
            return [
                'label' => 'Успешный, но нерегулярный',
                'description' => 'Результаты хорошие, но стоит поддержать стабильный темп.',
                'class' => 'bg-violet-50 text-violet-600 dark:bg-violet-500/15 dark:text-violet-300',
            ];
        }

        if ($engagementScore >= 60 && $averagePercent >= 75) {
            return [
                'label' => 'Стабильный профиль',
                'description' => 'Активность и результативность находятся в рабочем диапазоне.',
                'class' => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-300',
            ];
        }

        return [
            'label' => 'Нестабильный темп',
            'description' => 'Показатели смешанные: куратор предлагает двигаться малыми регулярными шагами.',
            'class' => 'bg-blue-50 text-blue-600 dark:bg-blue-500/15 dark:text-blue-300',
        ];
    }

    private function getInterventionSignals(
        int $riskScore,
        float $averagePercent,
        int $failedAttemptsCount,
        ?int $daysSinceLastAttempt,
        array $paceIndicator,
        Collection $weakZones
    ): Collection {
        $signals = collect();

        if ($riskScore >= 65) {
            $signals->push([
                'level' => 'Высокий',
                'title' => 'Нужна консультация',
                'description' => 'Интегральный риск выше 65/100. Лучше подключить преподавателя.',
                'class' => 'bg-red-50 text-red-700 dark:bg-red-500/15 dark:text-red-200',
            ]);
        }

        if (($daysSinceLastAttempt ?? 999) > 10) {
            $signals->push([
                'level' => 'Средний',
                'title' => 'Пауза в активности',
                'description' => 'Нет свежих попыток больше 10 дней. Нужен короткий возвратный шаг.',
                'class' => 'bg-amber-50 text-amber-700 dark:bg-amber-500/15 dark:text-amber-200',
            ]);
        }

        if (($paceIndicator['expected_percent'] ?? 0) > 0 && str_contains($paceIndicator['label'] ?? '', 'отставание')) {
            $signals->push([
                'level' => 'Средний',
                'title' => 'Отставание от траектории',
                'description' => 'Фактический прогресс ниже ожидаемого темпа обучения.',
                'class' => 'bg-amber-50 text-amber-700 dark:bg-amber-500/15 dark:text-amber-200',
            ]);
        }

        if ($failedAttemptsCount >= 3 || ($averagePercent > 0 && $averagePercent < 60)) {
            $signals->push([
                'level' => 'Высокий',
                'title' => 'Устойчивые ошибки',
                'description' => 'Результаты показывают, что требуется разбор типовых ошибок.',
                'class' => 'bg-red-50 text-red-700 dark:bg-red-500/15 dark:text-red-200',
            ]);
        }

        if ($weakZones->isNotEmpty()) {
            $signals->push([
                'level' => 'Тематический',
                'title' => 'Есть слабые темы',
                'description' => 'Главная проблемная зона: ' . $weakZones->first()['topic'] . '.',
                'class' => 'bg-orange-50 text-orange-700 dark:bg-orange-500/15 dark:text-orange-200',
            ]);
        }

        if ($signals->isEmpty()) {
            $signals->push([
                'level' => 'Норма',
                'title' => 'Вмешательство не требуется',
                'description' => 'Куратор не видит критичных причин для срочной помощи.',
                'class' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-200',
            ]);
        }

        return $signals->take(4)->values();
    }

    private function getWeeklyPlan(
        array $nextBestStep,
        Collection $priorityActions,
        Collection $recommendedMaterials,
        Collection $upcomingEvents,
        Collection $weakZones
    ): Collection {
        $plan = collect([
            [
                'day' => 'Сегодня',
                'title' => $nextBestStep['title'],
                'description' => $nextBestStep['description'],
                'route' => $nextBestStep['route'],
                'label' => $nextBestStep['label'],
            ],
        ]);

        $secondAction = $priorityActions->skip(1)->first();
        if ($secondAction) {
            $plan->push([
                'day' => 'Завтра',
                'title' => $secondAction['title'],
                'description' => $secondAction['description'],
                'route' => $secondAction['route'],
                'label' => $secondAction['label'],
            ]);
        }

        $material = $recommendedMaterials->first();
        if ($material) {
            $plan->push([
                'day' => 'Через 2 дня',
                'title' => 'Повторить материал',
                'description' => $material->title . ' поможет закрыть пробелы перед следующим тестом.',
                'route' => route('materials.show', $material),
                'label' => 'Материал',
            ]);
        }

        if ($weakZones->isNotEmpty()) {
            $plan->push([
                'day' => 'Конец недели',
                'title' => 'Проверить слабую тему',
                'description' => 'Сделай повторение по теме "' . $weakZones->first()['topic'] . '" и сравни результат с текущим.',
                'route' => route('courses.index'),
                'label' => 'К курсам',
            ]);
        }

        $event = $upcomingEvents->first();
        if ($event) {
            $plan->push([
                'day' => 'Ближайшее занятие',
                'title' => $event->title ?? $event->course?->title ?? 'Занятие',
                'description' => 'Используй занятие для вопросов по слабым темам.',
                'route' => route('schedule.index'),
                'label' => 'Расписание',
            ]);
        }

        while ($plan->count() < 4) {
            $plan->push([
                'day' => $plan->count() === 1 ? 'Завтра' : 'Конец недели',
                'title' => 'Короткая учебная сессия',
                'description' => 'Повтори один материал и зафиксируй прогресс через тест или просмотр результатов.',
                'route' => route('courses.index'),
                'label' => 'К курсам',
            ]);
        }

        return $plan->take(4)->values();
    }

    private function getRiskForecast(
        int $riskScore,
        int $progressPercent,
        float $averagePercent,
        ?int $daysSinceLastAttempt
    ): array {
        $progressBonus = $progressPercent < 100 ? 12 : 4;
        $scoreBonus = $averagePercent < 75 ? 10 : 5;
        $inactivityPenalty = ($daysSinceLastAttempt ?? 0) > 7 ? 10 : 14;

        return [
            [
                'label' => 'Пройти 2 приоритетных теста',
                'value' => max(0, $riskScore - $progressBonus - $scoreBonus),
                'hint' => 'Риск снизится за счёт прогресса и свежей активности.',
                'class' => 'text-emerald-600 dark:text-emerald-300',
            ],
            [
                'label' => 'Повторить слабую тему',
                'value' => max(0, $riskScore - 10),
                'hint' => 'Снижается влияние повторных ошибок.',
                'class' => 'text-blue-600 dark:text-blue-300',
            ],
            [
                'label' => 'Не заниматься 7 дней',
                'value' => min(100, $riskScore + $inactivityPenalty),
                'hint' => 'Риск вырастет из-за паузы в активности.',
                'class' => 'text-red-600 dark:text-red-300',
            ],
        ];
    }

    private function getDigitalProfile(
        string $learningGoal,
        int $engagementScore,
        array $behaviorScenario,
        array $paceIndicator,
        Collection $competencyMap,
        Collection $interventionSignals
    ): array {
        $averageMastery = $competencyMap->isNotEmpty()
            ? (int) round($competencyMap->avg('mastery_percent'))
            : null;

        return [
            [
                'label' => 'Цель',
                'value' => $this->learningGoalOptions()[$learningGoal]['label'] ?? 'Учебная цель',
                'hint' => 'Используется при выборе приоритетов',
            ],
            [
                'label' => 'Вовлечённость',
                'value' => $engagementScore . '/100',
                'hint' => 'Попытки, регулярность, прогресс и обращения',
            ],
            [
                'label' => 'Тип поведения',
                'value' => $behaviorScenario['label'],
                'hint' => 'Классификация по активности и результату',
            ],
            [
                'label' => 'Темп',
                'value' => $paceIndicator['label'],
                'hint' => 'Сравнение факта с ожидаемым прогрессом',
            ],
            [
                'label' => 'Освоение тем',
                'value' => $averageMastery === null ? 'нет данных' : $averageMastery . '%',
                'hint' => 'Среднее по темам с ответами',
            ],
            [
                'label' => 'Сигналы',
                'value' => (string) $interventionSignals->count(),
                'hint' => 'Причины для внимания или поддержки',
            ],
        ];
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

    private function getStudentRiskScore(
        int $availableTestsCount,
        int $progressPercent,
        float $averagePercent,
        int $failedAttemptsCount,
        ?int $daysSinceLastAttempt,
        int $currentStreak
    ): int {
        if ($availableTestsCount === 0) {
            return 15;
        }

        $score = 0;

        if ($progressPercent < 40) {
            $score += 30;
        } elseif ($progressPercent < 70) {
            $score += 15;
        }

        if ($averagePercent > 0 && $averagePercent < 60) {
            $score += 30;
        } elseif ($averagePercent > 0 && $averagePercent < 75) {
            $score += 15;
        }

        if ($failedAttemptsCount >= 3) {
            $score += 20;
        } elseif ($failedAttemptsCount > 0) {
            $score += 10;
        }

        if ($daysSinceLastAttempt === null) {
            $score += 20;
        } elseif ($daysSinceLastAttempt > 14) {
            $score += 25;
        } elseif ($daysSinceLastAttempt > 7) {
            $score += 15;
        }

        if ($currentStreak === 0) {
            $score += 10;
        }

        return min(100, $score);
    }

    private function getStudentRiskProfile(int $riskScore): array
    {
        if ($riskScore >= 65) {
            return [
                'label' => 'Нужна поддержка',
                'description' => 'Куратор видит риск отставания: стоит начать с ближайшего теста и повторить материалы по слабым темам.',
                'class' => 'bg-red-50 text-red-600 ring-red-100 dark:bg-red-500/15 dark:text-red-300 dark:ring-red-400/20',
                'bar' => 'bg-red-500',
            ];
        }

        if ($riskScore >= 35) {
            return [
                'label' => 'Средний риск',
                'description' => 'Темп обучения можно стабилизировать: закрой незавершённые тесты и поддерживай регулярность.',
                'class' => 'bg-amber-50 text-amber-600 ring-amber-100 dark:bg-amber-500/15 dark:text-amber-300 dark:ring-amber-400/20',
                'bar' => 'bg-amber-500',
            ];
        }

        return [
            'label' => 'Стабильный темп',
            'description' => 'Система не видит критичных рисков. Продолжай закрывать тесты и закреплять сильные результаты.',
            'class' => 'bg-emerald-50 text-emerald-600 ring-emerald-100 dark:bg-emerald-500/15 dark:text-emerald-300 dark:ring-emerald-400/20',
            'bar' => 'bg-emerald-500',
        ];
    }

    private function getCuratorPriorityActions(
        Collection $courseIds,
        int $availableTestsCount,
        int $progressPercent,
        float $averagePercent,
        ?int $daysSinceLastAttempt,
        int $currentStreak,
        int $openTicketsCount,
        Collection $recommendedTests,
        string $learningGoal,
        Collection $weakZones
    ): Collection {
        $actions = collect();
        $topTest = $recommendedTests->first();
        $goalLabel = $this->learningGoalOptions()[$learningGoal]['label'] ?? 'учебная цель';

        if ($courseIds->isEmpty()) {
            $actions->push([
                'title' => 'Выбрать курс',
                'description' => 'Запишись на доступный курс, чтобы куратор смог построить персональный план.',
                'route' => route('courses.index'),
                'label' => 'Открыть курсы',
            ]);
        }

        if ($availableTestsCount > 0 && $progressPercent < 100 && $topTest) {
            $actions->push([
                'title' => $topTest['status'] . ' тест',
                'description' => $topTest['test']->title . ': ' . $topTest['hint'] . '.',
                'route' => route('tests.take', $topTest['test']),
                'label' => 'Перейти к тесту',
            ]);
        }

        if ($weakZones->isNotEmpty() && in_array($learningGoal, ['score_80', 'exam_prep'], true)) {
            $weakZone = $weakZones->first();

            $actions->push([
                'title' => 'Разобрать слабую тему',
                'description' => 'Чаще всего ошибки встречаются в теме "' . $weakZone['topic'] . '". Это влияет на цель: ' . $goalLabel . '.',
                'route' => route('courses.index'),
                'label' => 'К материалам',
            ]);
        }

        if ($averagePercent > 0 && $averagePercent < 75) {
            $actions->push([
                'title' => 'Повторить слабые темы',
                'description' => 'Средний результат ниже 75%, поэтому сначала закрепи материалы по курсам с незакрытыми тестами.',
                'route' => route('courses.index'),
                'label' => 'Открыть курсы',
            ]);
        }

        if ($daysSinceLastAttempt === null || $daysSinceLastAttempt > 7 || $currentStreak === 0) {
            $actions->push([
                'title' => 'Вернуться в учебный ритм',
                'description' => 'Запланируй короткую учебную сессию и проверь ближайшее расписание.',
                'route' => route('schedule.index'),
                'label' => 'Расписание',
            ]);
        }

        if ($openTicketsCount > 0) {
            $actions->push([
                'title' => 'Проверить ответы',
                'description' => 'Есть открытые обращения. Ответ преподавателя может помочь быстрее закрыть проблему.',
                'route' => route('support-tickets.index'),
                'label' => 'Мои заявки',
            ]);
        }

        if ($actions->isEmpty()) {
            $actions->push([
                'title' => 'Закрепить результат',
                'description' => 'Темп стабильный. Продолжай проходить тесты и отслеживай новые материалы.',
                'route' => route('courses.index'),
                'label' => 'К курсам',
            ]);
        }

        return $actions->take(4)->values();
    }
}
