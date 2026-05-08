<?php

namespace App\Http\Controllers;

use App\Helpers\ActionLogger;
use App\Models\Course;
use App\Models\Test;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TestController extends Controller
{
    private function checkManageAccess(?Course $course = null, ?Test $test = null): void
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            return;
        }

        if ($user->role === 'teacher') {
            if ($course && $course->teacher_id === $user->id) {
                return;
            }

            if ($test && $test->author_id === $user->id) {
                return;
            }
        }

        abort(403);
    }

    private function checkViewAccess(Test $test): void
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            return;
        }

        if ($user->role === 'teacher' && $test->author_id === $user->id) {
            return;
        }

        if ($user->role === 'student') {
            $isEnrolled = $user->courses()
                ->where('courses.id', $test->course_id)
                ->exists();

            if ($isEnrolled && $test->is_published && !$test->is_archived) {
                return;
            }
        }

        abort(403);
    }

    public function index(Course $course)
    {
        $user = Auth::user();

        if ($user->role === 'student') {
            $isEnrolled = $user->courses()
                ->where('courses.id', $course->id)
                ->exists();

            if (!$isEnrolled) {
                abort(403);
            }

            $tests = $course->tests()
                ->with('author')
                ->where('is_published', true)
                ->where('is_archived', false)
                ->latest()
                ->get();
        } else {
            $this->checkManageAccess($course);

            $tests = $course->tests()
                ->with('author')
                ->latest()
                ->get();
        }

        return view('tests.index', compact('course', 'tests'));
    }

    public function create(Course $course)
    {
        $this->checkManageAccess($course);

        return view('tests.create', compact('course'));
    }

    public function store(Request $request, Course $course)
    {
        $this->checkManageAccess($course);

        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'time_limit' => ['nullable', 'integer', 'min:1'],
            'attempts_limit' => ['nullable', 'integer', 'min:1'],
        ]);

        $test = Test::create([
            'course_id' => $course->id,
            'author_id' => Auth::id(),
            'title' => $request->title,
            'description' => $request->description,
            'time_limit' => $request->time_limit,
            'attempts_limit' => $request->attempts_limit,
            'is_published' => $request->has('is_published'),
            'is_archived' => false,
        ]);

        ActionLogger::log(
            'Создание теста',
            'Создан тест: ' . $test->title . ' в курсе: ' . $course->title,
            $request
        );

        return redirect()
            ->route('tests.index', $course)
            ->with('success', 'Тест создан');
    }

    public function show(Test $test)
    {
        $this->checkViewAccess($test);

        $test->load(['course', 'author', 'questions.answers']);

        return view('tests.show', compact('test'));
    }

    public function edit(Test $test)
    {
        $this->checkManageAccess(null, $test);

        return view('tests.edit', compact('test'));
    }

    public function update(Request $request, Test $test)
    {
        $this->checkManageAccess(null, $test);

        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'time_limit' => ['nullable', 'integer', 'min:1'],
            'attempts_limit' => ['nullable', 'integer', 'min:1'],
        ]);

        $oldTitle = $test->title;
        $course = $test->course;

        $test->update([
            'title' => $request->title,
            'description' => $request->description,
            'time_limit' => $request->time_limit,
            'attempts_limit' => $request->attempts_limit,
            'is_published' => $request->has('is_published'),
        ]);

        ActionLogger::log(
            'Обновление теста',
            'Обновлён тест: ' . $oldTitle . ' → ' . $test->title . ' в курсе: ' . $course->title,
            $request
        );

        return redirect()
            ->route('tests.index', $test->course)
            ->with('success', 'Тест обновлён');
    }

    public function destroy(Test $test)
    {
        $this->checkManageAccess(null, $test);

        $course = $test->course;
        $title = $test->title;

        $test->delete();

        ActionLogger::log(
            'Удаление теста',
            'Удалён тест: ' . $title . ' из курса: ' . $course->title,
            request()
        );

        return redirect()
            ->route('tests.index', $course)
            ->with('success', 'Тест удалён');
    }
}