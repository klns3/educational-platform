<?php

namespace App\Http\Controllers;

use App\Helpers\ActionLogger;
use App\Models\StudentAnswer;
use App\Models\Test;
use App\Models\TestAttempt;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class TestPassingController extends Controller
{
    private function checkTestAccess(Test $test): void
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

    public function take(Test $test)
    {
        $test->load('questions.answers');

        $this->checkTestAccess($test);

        $isPrivilegedAttempt = in_array(Auth::user()->role, ['admin', 'teacher'], true);

        $attemptsCount = TestAttempt::where('test_id', $test->id)
            ->where('user_id', Auth::id())
            ->count();

        if (!$isPrivilegedAttempt && $test->attempts_limit && $attemptsCount >= $test->attempts_limit) {
            return redirect()
                ->route('tests.show', $test)
                ->with('error', 'Вы уже использовали максимальное количество попыток для этого теста.');
        }

        $sessionKey = 'test_started_at_' . $test->id;

        if (!session()->has($sessionKey)) {
            session([
                $sessionKey => now()->format('Y-m-d H:i:s'),
            ]);

            ActionLogger::log(
                'Начало теста',
                'Пользователь начал прохождение теста: ' . $test->title,
                request()
            );
        }

        $startedAt = Carbon::parse(session($sessionKey));
        $remainingSeconds = null;

        if ($test->time_limit) {
            $deadline = $startedAt->copy()->addMinutes($test->time_limit);
            $remainingSeconds = (int) max(0, ceil(now()->diffInSeconds($deadline, false)));
        }

        return view('tests.take', compact('test', 'remainingSeconds'));
    }

    public function submit(Request $request, Test $test)
    {
        $test->load('questions.answers');

        $this->checkTestAccess($test);

        $isPrivilegedAttempt = in_array(Auth::user()->role, ['admin', 'teacher'], true);

        $attemptsCount = TestAttempt::where('test_id', $test->id)
            ->where('user_id', Auth::id())
            ->count();

        if (!$isPrivilegedAttempt && $test->attempts_limit && $attemptsCount >= $test->attempts_limit) {
            return redirect()
                ->route('tests.show', $test)
                ->with('error', 'Вы уже использовали максимальное количество попыток для этого теста.');
        }

        $sessionKey = 'test_started_at_' . $test->id;
        $startedAt = session($sessionKey) ?? now()->format('Y-m-d H:i:s');
        $startedAtCarbon = Carbon::parse($startedAt);

        $maxScore = $test->questions->sum('points');
        $score = 0;

        $attempt = TestAttempt::create([
            'test_id' => $test->id,
            'user_id' => Auth::id(),
            'score' => 0,
            'max_score' => $maxScore,
            'started_at' => $startedAtCarbon->format('Y-m-d H:i:s'),
            'finished_at' => now()->format('Y-m-d H:i:s'),
        ]);

        foreach ($test->questions as $question) {
            $studentAnswer = $request->input('answers.' . $question->id);
            $points = 0;
            $isCorrect = false;

            if ($question->question_type === 'single') {
                $correctAnswer = $question->answers
                    ->where('is_correct', true)
                    ->first();

                if ($studentAnswer !== null && $correctAnswer) {
                    $isCorrect = (int) $studentAnswer === (int) $correctAnswer->id;
                    $points = $isCorrect ? $question->points : 0;
                }

                StudentAnswer::create([
                    'attempt_id' => $attempt->id,
                    'question_id' => $question->id,
                    'answer_id' => $studentAnswer,
                    'text_answer' => null,
                    'is_correct' => $isCorrect,
                    'points_awarded' => $points,
                ]);
            }

            if ($question->question_type === 'multiple') {
                $correctIds = $question->answers
                    ->where('is_correct', true)
                    ->pluck('id')
                    ->map(fn ($id) => (int) $id)
                    ->sort()
                    ->values()
                    ->toArray();

                $selectedIds = collect($studentAnswer ?? [])
                    ->map(fn ($id) => (int) $id)
                    ->sort()
                    ->values()
                    ->toArray();

                if (!empty($selectedIds)) {
                    $isCorrect = $correctIds === $selectedIds;
                    $points = $isCorrect ? $question->points : 0;
                }

                StudentAnswer::create([
                    'attempt_id' => $attempt->id,
                    'question_id' => $question->id,
                    'answer_id' => null,
                    'text_answer' => json_encode($selectedIds),
                    'is_correct' => $isCorrect,
                    'points_awarded' => $points,
                ]);
            }

            if ($question->question_type === 'text') {
                $correctAnswer = $question->answers
                    ->where('is_correct', true)
                    ->first();

                $studentText = Str::lower(trim((string) $studentAnswer));
                $correctText = $correctAnswer
                    ? Str::lower(trim($correctAnswer->answer_text))
                    : '';

                if ($studentText !== '' && $correctText !== '') {
                    $isCorrect = $studentText === $correctText;
                    $points = $isCorrect ? $question->points : 0;
                }

                StudentAnswer::create([
                    'attempt_id' => $attempt->id,
                    'question_id' => $question->id,
                    'answer_id' => null,
                    'text_answer' => $studentAnswer,
                    'is_correct' => $isCorrect,
                    'points_awarded' => $points,
                ]);
            }

            $score += $points;
        }

        $attempt->update([
            'score' => $score,
        ]);

        session()->forget($sessionKey);

        ActionLogger::log(
            'Завершение теста',
            'Пользователь завершил тест: ' . $test->title . '. Результат: ' . $score . ' из ' . $maxScore,
            $request
        );

        if (Auth::user()->role === 'teacher') {
            return redirect()
                ->route('tests.show', $test)
                ->with('success', 'Тест завершён. Попытка преподавателя не отображается в результатах и статистике.');
        }

        return redirect()->route('tests.result', $attempt);
    }

    public function result(TestAttempt $attempt)
    {
        $attempt->loadMissing(['user', 'test']);
        $user = Auth::user();

        if ($attempt->user?->role === 'teacher') {
            abort(404);
        }

        if ($user->role === 'student' && $attempt->user_id !== $user->id) {
            abort(403);
        }

        if ($user->role === 'teacher' && $attempt->test?->author_id !== $user->id) {
            abort(403);
        }

        $attempt->load([
            'test',
            'studentAnswers.question.answers',
            'studentAnswers.answer',
        ]);

        return view('tests.result', compact('attempt'));
    }
}
