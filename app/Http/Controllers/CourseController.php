<?php

namespace App\Http\Controllers;

use App\Helpers\ActionLogger;
use App\Models\Course;
use App\Models\TestAttempt;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CourseController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->role === 'student') {
            $courses = $user->courses()
                ->with('teacher')
                ->withCount([
                    'tests as available_tests_count' => fn ($query) => $query
                        ->where('is_published', true)
                        ->where('is_archived', false),
                ])
                ->latest()
                ->get();

            $this->attachStudentProgress($courses, $user->id);
        } elseif ($user->role === 'teacher') {
            $courses = Course::with(['teacher', 'students'])
                ->withCount('students')
                ->where('teacher_id', $user->id)
                ->latest()
                ->get();
        } else {
            $courses = Course::with(['teacher', 'students'])
                ->withCount('students')
                ->latest()
                ->get();
        }

        return view('courses.index', compact('courses'));
    }

    public function create()
    {
        return view('courses.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'cover' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $coverPath = $request->hasFile('cover')
            ? $request->file('cover')->store('course-covers', 'public')
            : null;

        $course = Course::create([
            'title' => $request->title,
            'description' => $request->description,
            'cover' => $coverPath,
            'teacher_id' => Auth::id(),
        ]);

        ActionLogger::log(
            'Создание курса',
            'Создан курс: ' . $course->title,
            $request
        );

        return redirect()->route('courses.index')
            ->with('success', 'Курс успешно создан');
    }

    public function show(Course $course)
    {
        $this->checkCourseAccess($course);

        $course->load(['teacher', 'students', 'materials', 'tests']);

        if (Auth::user()->role === 'student') {
            $course->loadCount([
                'tests as available_tests_count' => fn ($query) => $query
                    ->where('is_published', true)
                    ->where('is_archived', false),
            ]);

            $this->attachStudentProgress(new Collection([$course]), Auth::id());
        }

        return view('courses.show', compact('course'));
    }

    public function edit(Course $course)
    {
        $this->checkManageAccess($course);

        return view('courses.edit', compact('course'));
    }

    public function update(Request $request, Course $course)
    {
        $this->checkManageAccess($course);

        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'cover' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $oldTitle = $course->title;
        $oldCover = $course->cover;

        $course->fill($request->only('title', 'description'));

        if ($request->hasFile('cover')) {
            if ($oldCover) {
                Storage::disk('public')->delete($oldCover);
            }

            $course->cover = $request->file('cover')->store('course-covers', 'public');
        }

        $course->save();

        ActionLogger::log(
            'Обновление курса',
            'Обновлён курс: ' . $oldTitle . ' → ' . $course->title,
            $request
        );

        return redirect()->route('courses.index')
            ->with('success', 'Курс обновлён');
    }

    public function destroy(Course $course)
    {
        $this->checkManageAccess($course);

        $title = $course->title;

        if ($course->cover) {
            Storage::disk('public')->delete($course->cover);
        }

        $course->delete();

        ActionLogger::log(
            'Удаление курса',
            'Удалён курс: ' . $title,
            request()
        );

        return redirect()->route('courses.index')
            ->with('success', 'Курс удалён');
    }

    public function students(Course $course)
    {
        $this->checkManageAccess($course);

        $course->load('students');

        $students = User::where('role', 'student')
            ->orderBy('name')
            ->get();

        return view('courses.students', compact('course', 'students'));
    }

    public function syncStudents(Request $request, Course $course)
    {
        $this->checkManageAccess($course);

        $request->validate([
            'students' => ['nullable', 'array'],
            'students.*' => ['integer', 'exists:users,id'],
        ]);

        $studentIds = User::whereIn('id', $request->students ?? [])
            ->where('role', 'student')
            ->pluck('id')
            ->toArray();

        $course->students()->sync($studentIds);

        ActionLogger::log(
            'Обновление студентов курса',
            'Обновлён список студентов курса: ' . $course->title . '. Количество студентов: ' . count($studentIds),
            $request
        );

        return redirect()
            ->route('courses.students', $course)
            ->with('success', 'Список студентов курса обновлён');
    }

    private function checkCourseAccess(Course $course): void
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            return;
        }

        if ($user->role === 'teacher' && $course->teacher_id === $user->id) {
            return;
        }

        if ($user->role === 'student' && $course->students()->where('users.id', $user->id)->exists()) {
            return;
        }

        abort(403);
    }

    private function checkManageAccess(Course $course): void
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            return;
        }

        if ($user->role === 'teacher' && $course->teacher_id === $user->id) {
            return;
        }

        abort(403);
    }

    private function attachStudentProgress(Collection $courses, int $userId): void
    {
        if ($courses->isEmpty()) {
            return;
        }

        $completedCounts = TestAttempt::query()
            ->selectRaw('tests.course_id, COUNT(DISTINCT test_attempts.test_id) as completed_tests_count')
            ->join('tests', 'tests.id', '=', 'test_attempts.test_id')
            ->where('test_attempts.user_id', $userId)
            ->where('tests.is_published', true)
            ->where('tests.is_archived', false)
            ->whereIn('tests.course_id', $courses->pluck('id'))
            ->groupBy('tests.course_id')
            ->pluck('completed_tests_count', 'tests.course_id');

        foreach ($courses as $course) {
            $availableTestsCount = (int) ($course->available_tests_count ?? 0);
            $completedTestsCount = min(
                $availableTestsCount,
                (int) ($completedCounts[$course->id] ?? 0)
            );

            $course->setAttribute('completed_tests_count', $completedTestsCount);
            $course->setAttribute(
                'progress_percent',
                $availableTestsCount > 0
                    ? (int) round(($completedTestsCount / $availableTestsCount) * 100)
                    : 0
            );
        }
    }
}
