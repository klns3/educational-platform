<?php

namespace App\Http\Controllers;

use App\Helpers\ActionLogger;
use App\Models\Answer;
use App\Models\Question;
use App\Models\Test;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuestionController extends Controller
{
    private function checkAccess()
    {
        if (!in_array(Auth::user()->role, ['admin', 'teacher'])) {
            abort(403);
        }
    }

    public function index(Test $test)
    {
        $questions = $test->questions()
            ->with('answers')
            ->latest()
            ->get();

        return view('questions.index', compact('test', 'questions'));
    }

    public function create(Test $test)
    {
        $this->checkAccess();

        return view('questions.create', compact('test'));
    }

    public function store(Request $request, Test $test)
    {
        $this->checkAccess();

        $request->validate([
            'question_text' => ['required', 'string'],
            'question_type' => ['required', 'in:single,multiple,text'],
            'points' => ['required', 'integer', 'min:1'],
            'answers' => ['nullable', 'array'],
            'answers.*' => ['nullable', 'string'],
            'correct_answer' => ['nullable'],
            'correct_answers' => ['nullable', 'array'],
        ]);

        $question = Question::create([
            'test_id' => $test->id,
            'question_text' => $request->question_text,
            'question_type' => $request->question_type,
            'points' => $request->points,
        ]);

        $this->saveAnswers($request, $question);

        ActionLogger::log(
            'Создание вопроса',
            'Добавлен вопрос к тесту "' . $test->title . '": ' . mb_strimwidth($question->question_text, 0, 80, '...'),
            $request
        );

        return redirect()
            ->route('questions.index', $test)
            ->with('success', 'Вопрос добавлен');
    }

    public function edit(Question $question)
    {
        $this->checkAccess();

        $question->load('answers');

        return view('questions.edit', compact('question'));
    }

    public function update(Request $request, Question $question)
    {
        $this->checkAccess();

        $request->validate([
            'question_text' => ['required', 'string'],
            'question_type' => ['required', 'in:single,multiple,text'],
            'points' => ['required', 'integer', 'min:1'],
            'answers' => ['nullable', 'array'],
            'answers.*' => ['nullable', 'string'],
            'correct_answer' => ['nullable'],
            'correct_answers' => ['nullable', 'array'],
        ]);

        $oldText = $question->question_text;
        $test = $question->test;

        $question->update([
            'question_text' => $request->question_text,
            'question_type' => $request->question_type,
            'points' => $request->points,
        ]);

        $question->answers()->delete();

        $this->saveAnswers($request, $question);

        ActionLogger::log(
            'Обновление вопроса',
            'Обновлён вопрос в тесте "' . $test->title . '": ' .
            mb_strimwidth($oldText, 0, 60, '...') . ' → ' .
            mb_strimwidth($question->question_text, 0, 60, '...'),
            $request
        );

        return redirect()
            ->route('questions.index', $question->test)
            ->with('success', 'Вопрос обновлён');
    }

    private function saveAnswers(Request $request, Question $question): void
    {
        if ($request->question_type === 'text') {
            $textAnswer = trim((string) ($request->answers[0] ?? ''));

            if ($textAnswer !== '') {
                Answer::create([
                    'question_id' => $question->id,
                    'answer_text' => $textAnswer,
                    'is_correct' => true,
                ]);
            }

            return;
        }

        foreach ($request->answers ?? [] as $index => $answerText) {
            if (!empty($answerText)) {
                Answer::create([
                    'question_id' => $question->id,
                    'answer_text' => $answerText,
                    'is_correct' => $request->question_type === 'single'
                        ? (string) $request->correct_answer === (string) $index
                        : in_array($index, $request->correct_answers ?? []),
                ]);
            }
        }
    }

    public function destroy(Question $question)
    {
        $this->checkAccess();

        $test = $question->test;
        $text = $question->question_text;

        $question->delete();

        ActionLogger::log(
            'Удаление вопроса',
            'Удалён вопрос из теста "' . $test->title . '": ' . mb_strimwidth($text, 0, 80, '...'),
            request()
        );

        return redirect()
            ->route('questions.index', $test)
            ->with('success', 'Вопрос удалён');
    }
}