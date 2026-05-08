<?php

namespace App\Http\Controllers;

use App\Helpers\ActionLogger;
use App\Models\ClassGroup;
use App\Models\Course;
use App\Models\ScheduleEvent;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        $weekStart = $request->filled('week_start')
            ? Carbon::parse($request->week_start)->startOfWeek(Carbon::MONDAY)
            : now()->startOfWeek(Carbon::MONDAY);
        $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);

        $events = $this->visibleEventsQuery($user)
            ->with(['teacher', 'classGroup', 'course'])
            ->forWeek('starts_at', $weekStart, $weekEnd)
            ->orderBy('starts_at')
            ->get();

        $weekDays = collect(range(0, 6))->map(
            fn (int $dayOffset) => $weekStart->copy()->addDays($dayOffset)
        );

        return view('schedule.index', [
            'eventsByDay' => $events->groupBy(fn (ScheduleEvent $event) => $event->starts_at->toDateString()),
            'todayEvents' => $events
                ->filter(fn (ScheduleEvent $event) => $event->starts_at->isToday())
                ->values(),
            'weekDays' => $weekDays,
            'weekStart' => $weekStart,
            'weekEnd' => $weekEnd,
            'previousWeekStart' => $weekStart->copy()->subWeek()->toDateString(),
            'nextWeekStart' => $weekStart->copy()->addWeek()->toDateString(),
            'canManageSchedule' => in_array($user->role, ['admin', 'teacher']),
            'hasAvailableGroups' => $this->availableGroupsForManager($user)->isNotEmpty(),
            'studentHasGroup' => $user->role !== 'student' || $user->class_group_id !== null,
        ]);
    }

    public function create(): View
    {
        $this->authorizeScheduleManager();

        return view('schedule.create', [
            'groups' => $this->availableGroupsForManager(Auth::user()),
            'courses' => $this->availableCoursesForManager(Auth::user()),
            'teachers' => $this->availableTeachersForManager(Auth::user()),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeScheduleManager();

        $manager = Auth::user();
        $groups = $this->availableGroupsForManager($manager);
        $courses = $this->availableCoursesForManager($manager);
        $teachers = $this->availableTeachersForManager($manager);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', Rule::in(ScheduleEvent::typeOptions())],
            'teacher_id' => ['nullable', 'integer', 'exists:users,id'],
            'class_group_id' => ['required', 'integer', 'exists:class_groups,id'],
            'course_id' => ['nullable', 'integer', 'exists:courses,id'],
            'location' => ['nullable', 'string', 'max:255'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
        ]);

        $this->ensureAllowedGroup($groups, (int) $validated['class_group_id']);
        $this->ensureAllowedCourse($courses, $validated['course_id'] ?? null);
        $teacherId = $this->resolveTeacherId($manager, $teachers, $validated['teacher_id'] ?? null);

        $event = ScheduleEvent::create([
            'teacher_id' => $teacherId,
            'class_group_id' => $validated['class_group_id'],
            'course_id' => $validated['course_id'] ?? null,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'type' => $validated['type'],
            'location' => $validated['location'] ?? null,
            'starts_at' => Carbon::parse($validated['starts_at']),
            'ends_at' => Carbon::parse($validated['ends_at']),
        ]);

        ActionLogger::log(
            'Создание события расписания',
            'Создано событие расписания: ' . $event->title,
            $request
        );

        return redirect()
            ->route('schedule.index')
            ->with('success', 'Событие расписания создано');
    }

    public function edit(ScheduleEvent $scheduleEvent): View
    {
        $this->authorizeEventOwner($scheduleEvent);

        return view('schedule.edit', [
            'event' => $scheduleEvent,
            'groups' => $this->availableGroupsForManager(Auth::user()),
            'courses' => $this->availableCoursesForManager(Auth::user()),
            'teachers' => $this->availableTeachersForManager(Auth::user()),
        ]);
    }

    public function update(Request $request, ScheduleEvent $scheduleEvent): RedirectResponse
    {
        $this->authorizeEventOwner($scheduleEvent);

        $manager = Auth::user();
        $groups = $this->availableGroupsForManager($manager);
        $courses = $this->availableCoursesForManager($manager);
        $teachers = $this->availableTeachersForManager($manager);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', Rule::in(ScheduleEvent::typeOptions())],
            'teacher_id' => ['nullable', 'integer', 'exists:users,id'],
            'class_group_id' => ['required', 'integer', 'exists:class_groups,id'],
            'course_id' => ['nullable', 'integer', 'exists:courses,id'],
            'location' => ['nullable', 'string', 'max:255'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
        ]);

        $this->ensureAllowedGroup($groups, (int) $validated['class_group_id']);
        $this->ensureAllowedCourse($courses, $validated['course_id'] ?? null);
        $teacherId = $this->resolveTeacherId($manager, $teachers, $validated['teacher_id'] ?? null);

        $scheduleEvent->update([
            'teacher_id' => $teacherId,
            'class_group_id' => $validated['class_group_id'],
            'course_id' => $validated['course_id'] ?? null,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'type' => $validated['type'],
            'location' => $validated['location'] ?? null,
            'starts_at' => Carbon::parse($validated['starts_at']),
            'ends_at' => Carbon::parse($validated['ends_at']),
        ]);

        ActionLogger::log(
            'Обновление события расписания',
            'Обновлено событие расписания: ' . $scheduleEvent->title,
            $request
        );

        return redirect()
            ->route('schedule.index')
            ->with('success', 'Событие расписания обновлено');
    }

    public function destroy(ScheduleEvent $scheduleEvent): RedirectResponse
    {
        $this->authorizeEventOwner($scheduleEvent);

        $title = $scheduleEvent->title;
        $scheduleEvent->delete();

        ActionLogger::log(
            'Удаление события расписания',
            'Удалено событие расписания: ' . $title,
            request()
        );

        return redirect()
            ->route('schedule.index')
            ->with('success', 'Событие расписания удалено');
    }

    private function visibleEventsQuery($user): Builder
    {
        $query = ScheduleEvent::query();

        if ($user->role === 'teacher') {
            return $query->where('teacher_id', $user->id);
        }

        if ($user->role === 'student') {
            if (!$user->class_group_id) {
                return $query->whereRaw('1 = 0');
            }

            return $query->where('class_group_id', $user->class_group_id);
        }

        return $query;
    }

    private function availableGroupsForManager($user): Collection
    {
        if (!in_array($user->role, ['admin', 'teacher'])) {
            return new Collection();
        }

        return ClassGroup::query()
            ->orderBy('name')
            ->get();
    }

    private function availableCoursesForManager($user): Collection
    {
        if ($user->role === 'admin') {
            return Course::query()
                ->orderBy('title')
                ->get();
        }

        if ($user->role !== 'teacher') {
            return new Collection();
        }

        return Course::query()
            ->where('teacher_id', $user->id)
            ->orderBy('title')
            ->get();
    }

    private function availableTeachersForManager($user): Collection
    {
        if ($user->role === 'admin') {
            return User::query()
                ->where('role', 'teacher')
                ->orderBy('name')
                ->get();
        }

        if ($user->role !== 'teacher') {
            return new Collection();
        }

        return User::query()
            ->whereKey($user->id)
            ->get();
    }

    private function ensureAllowedGroup(Collection $groups, int $groupId): void
    {
        if (!$groups->contains('id', $groupId)) {
            abort(403);
        }
    }

    private function ensureAllowedCourse(Collection $courses, ?int $courseId): void
    {
        if ($courseId === null) {
            return;
        }

        if (!$courses->contains('id', $courseId)) {
            abort(403);
        }
    }

    private function authorizeScheduleManager(): void
    {
        if (!in_array(Auth::user()->role, ['admin', 'teacher'])) {
            abort(403);
        }
    }

    private function authorizeEventOwner(ScheduleEvent $scheduleEvent): void
    {
        $this->authorizeScheduleManager();

        if (Auth::user()->role === 'admin') {
            return;
        }

        if ($scheduleEvent->teacher_id !== Auth::id()) {
            abort(403);
        }
    }

    private function resolveTeacherId($manager, Collection $teachers, ?int $teacherId): int
    {
        if ($manager->role === 'teacher') {
            return $manager->id;
        }

        if ($teacherId === null || !$teachers->contains('id', $teacherId)) {
            abort(403);
        }

        return $teacherId;
    }
}
