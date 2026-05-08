<?php

namespace App\Http\Controllers;

use App\Helpers\ActionLogger;
use App\Models\Test;
use App\Models\TestAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResultController extends Controller
{
    public function myResults(Request $request)
    {
        $search = trim((string) $request->query('q', ''));

        if (Auth::user()->role === 'admin') {
            $tests = Test::with(['course', 'author'])
                ->withCount([
                    'attempts as student_attempts_count' => fn ($query) => $query->onlyStudentAttempts(),
                ])
                ->withAvg([
                    'attempts as student_average_score' => fn ($query) => $query->onlyStudentAttempts(),
                ], 'score');

            if ($search !== '') {
                $tests->where(function ($query) use ($search) {
                    $query->where('title', 'like', '%' . $search . '%')
                        ->orWhereHas('course', function ($courseQuery) use ($search) {
                            $courseQuery->where('title', 'like', '%' . $search . '%');
                        })
                        ->orWhereHas('author', function ($authorQuery) use ($search) {
                            $authorQuery->where('name', 'like', '%' . $search . '%');
                        });
                });
            }

            $tests = $tests
                ->latest()
                ->get();

            return view('results.teacher', [
                'tests' => $tests,
                'pageTitle' => 'Результаты всех тестов',
                'pageDescription' => 'Здесь отображаются результаты учеников по всем тестам всех курсов.',
                'emptyTitle' => 'Пока нет тестов',
                'emptyDescription' => 'После создания тестов здесь появятся результаты учеников по ним.',
                'showAuthor' => true,
                'search' => $search,
            ]);
        }

        if (Auth::user()->role === 'teacher') {
            $tests = Test::with('course')
                ->where('author_id', Auth::id())
                ->withCount([
                    'attempts as student_attempts_count' => fn ($query) => $query->onlyStudentAttempts(),
                ])
                ->withAvg([
                    'attempts as student_average_score' => fn ($query) => $query->onlyStudentAttempts(),
                ], 'score');

            if ($search !== '') {
                $tests->where(function ($query) use ($search) {
                    $query->where('title', 'like', '%' . $search . '%')
                        ->orWhereHas('course', function ($courseQuery) use ($search) {
                            $courseQuery->where('title', 'like', '%' . $search . '%');
                        });
                });
            }

            $tests = $tests
                ->latest()
                ->get();

            return view('results.teacher', [
                'tests' => $tests,
                'pageTitle' => 'Результаты по моим тестам',
                'pageDescription' => 'Здесь отображаются только результаты учеников по тестам, которые созданы вами.',
                'emptyTitle' => 'У вас пока нет тестов',
                'emptyDescription' => 'После создания тестов здесь появятся результаты учеников по ним.',
                'showAuthor' => false,
                'search' => $search,
            ]);
        }

        $attempts = TestAttempt::with('test')
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        return view('results.my', compact('attempts'));
    }

    public function testResults(Test $test)
    {
        if (!in_array(Auth::user()->role, ['admin', 'teacher'])) {
            abort(403);
        }

        if (Auth::user()->role === 'teacher' && $test->author_id !== Auth::id()) {
            abort(403);
        }

        $attempts = $test->attempts()
            ->with('user')
            ->onlyStudentAttempts()
            ->latest()
            ->get();

        ActionLogger::log(
            'Просмотр результатов теста',
            'Открыты результаты теста: ' . $test->title,
            request()
        );

        return view('results.test', compact('test', 'attempts'));
    }
}
