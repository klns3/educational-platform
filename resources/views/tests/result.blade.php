<x-app-layout>
    <div class="min-h-screen bg-[#f6f8fc] text-slate-950 transition-colors duration-300 dark:bg-[#07111f] dark:text-white">
        <div class="relative overflow-hidden">
            <div class="pointer-events-none absolute inset-0 hidden dark:block">
                <div class="absolute left-[16%] top-0 h-80 w-80 rounded-full bg-blue-600/20 blur-3xl"></div>
                <div class="absolute right-0 top-10 h-96 w-96 rounded-full bg-violet-600/20 blur-3xl"></div>
                <div class="absolute bottom-0 left-1/2 h-72 w-72 rounded-full bg-cyan-500/10 blur-3xl"></div>
            </div>

            <div class="relative mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
                @php
                    $percent = $attempt->max_score > 0
                        ? round(($attempt->score / $attempt->max_score) * 100)
                        : 0;

                    $status = $percent >= 70
                        ? ['text' => 'Хороший результат', 'class' => 'text-emerald-600 dark:text-emerald-300', 'bar' => 'bg-emerald-500']
                        : ($percent >= 40
                            ? ['text' => 'Средний результат', 'class' => 'text-amber-600 dark:text-amber-300', 'bar' => 'bg-amber-500']
                            : ['text' => 'Низкий результат', 'class' => 'text-red-600 dark:text-red-300', 'bar' => 'bg-red-500']);
                @endphp

                <section class="mb-7">
                    <p class="mb-4 inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700 dark:border-blue-400/20 dark:bg-blue-500/10 dark:text-blue-200">
                        Результаты
                    </p>

                    <h1 class="text-3xl font-black tracking-tight text-slate-950 sm:text-4xl dark:text-white">
                        Результат теста
                    </h1>

                    <p class="mt-3 max-w-2xl text-sm font-semibold text-slate-500 sm:text-base dark:text-slate-300">
                        {{ $attempt->test->title }}
                    </p>
                </section>

                <section class="mb-6 overflow-hidden rounded-[1.7rem] border border-white bg-white shadow-sm shadow-slate-200/70 dark:border-white/10 dark:bg-white/[0.04] dark:shadow-none">
                    <div class="grid gap-6 p-6 md:grid-cols-[1fr_220px] md:items-center">
                        <div>
                            <div class="mb-3 flex flex-wrap items-center gap-2">
                                <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-black text-blue-600 dark:bg-blue-500/15 dark:text-blue-300">
                                    {{ $percent }}%
                                </span>

                                <span class="rounded-full bg-slate-50 px-3 py-1 text-xs font-black dark:bg-white/10 {{ $status['class'] }}">
                                    {{ $status['text'] }}
                                </span>
                            </div>

                            <h2 class="text-2xl font-black text-slate-950 dark:text-white">
                                {{ $attempt->test->title }}
                            </h2>

                            <div class="mt-4 grid gap-2 text-sm font-semibold text-slate-500 sm:grid-cols-2 dark:text-slate-400">
                                <p>
                                    Баллы:
                                    <span class="font-black text-slate-900 dark:text-slate-100">
                                        {{ $attempt->score }} из {{ $attempt->max_score }}
                                    </span>
                                </p>

                                <p>
                                    Дата прохождения:
                                    <span class="font-black text-slate-900 dark:text-slate-100">
                                        {{ $attempt->finished_at }}
                                    </span>
                                </p>
                            </div>

                            <div class="mt-5 h-3 overflow-hidden rounded-full bg-slate-100 dark:bg-white/5">
                                <div class="h-full rounded-full {{ $status['bar'] }}"
                                     style="width: {{ min(100, max(0, $percent)) }}%"></div>
                            </div>
                        </div>

                        <div class="rounded-[1.4rem] border border-blue-200 bg-blue-50 p-5 text-center dark:border-blue-400/20 dark:bg-blue-500/10">
                            <p class="text-5xl font-black text-slate-950 dark:text-white">
                                {{ $percent }}%
                            </p>
                            <p class="mt-2 text-sm font-black {{ $status['class'] }}">
                                {{ $status['text'] }}
                            </p>
                        </div>
                    </div>
                </section>

                <section class="overflow-hidden rounded-[1.7rem] border border-white bg-white shadow-sm shadow-slate-200/70 dark:border-white/10 dark:bg-white/[0.04] dark:shadow-none">
                    <div class="border-b border-slate-100 px-6 py-5 dark:border-white/10">
                        <h2 class="text-xl font-black text-slate-950 dark:text-white">
                            Ответы
                        </h2>
                    </div>

                    <div class="grid divide-y divide-slate-100 dark:divide-white/10">
                        @foreach($attempt->studentAnswers as $studentAnswer)
                            @php
                                $question = $studentAnswer->question;
                                $correctAnswers = $question->answers->where('is_correct', true);
                                $studentSelectedIds = json_decode($studentAnswer->text_answer, true);

                                $questionTypeText = match ($question->question_type) {
                                    'single' => 'один правильный ответ',
                                    'multiple' => 'несколько правильных ответов',
                                    default => 'текстовый ответ',
                                };
                            @endphp

                            <article class="p-6">
                                <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                                    <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-black text-blue-600 dark:bg-blue-500/15 dark:text-blue-300">
                                        Вопрос {{ $loop->iteration }}
                                    </span>

                                    @if($studentAnswer->is_correct)
                                        <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-black text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-300">
                                            Верно
                                        </span>
                                    @else
                                        <span class="rounded-full bg-red-50 px-3 py-1 text-xs font-black text-red-600 dark:bg-red-500/15 dark:text-red-300">
                                            Неверно
                                        </span>
                                    @endif
                                </div>

                                <h3 class="text-lg font-black leading-7 text-slate-950 dark:text-white">
                                    {{ $question->question_text }}
                                </h3>

                                <p class="mt-2 text-sm font-semibold text-slate-500 dark:text-slate-400">
                                    Тип: {{ $questionTypeText }}
                                </p>

                                <div class="mt-5 grid gap-4 md:grid-cols-2">
                                    <div class="rounded-[1.4rem] border border-slate-200 bg-slate-50 p-5 dark:border-white/10 dark:bg-white/[0.03]">
                                        <p class="mb-3 text-sm font-black text-slate-950 dark:text-white">
                                            Ваш ответ
                                        </p>

                                        <div class="text-sm font-semibold leading-7 text-slate-600 dark:text-slate-300">
                                            @if($question->question_type === 'single')
                                                @php
                                                    $answer = $question->answers->firstWhere('id', $studentAnswer->answer_id);
                                                @endphp

                                                <p>{{ $answer->answer_text ?? 'Ответ не выбран' }}</p>
                                            @elseif($question->question_type === 'multiple')
                                                @php
                                                    $selectedAnswers = $question->answers->whereIn('id', $studentSelectedIds ?? []);
                                                @endphp

                                                @forelse($selectedAnswers as $answer)
                                                    <p>• {{ $answer->answer_text }}</p>
                                                @empty
                                                    <p>Ответ не выбран</p>
                                                @endforelse
                                            @else
                                                <p>{{ $studentAnswer->text_answer ?: 'Ответ не введён' }}</p>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="rounded-[1.4rem] border border-emerald-200 bg-emerald-50 p-5 dark:border-emerald-400/20 dark:bg-emerald-500/10">
                                        <p class="mb-3 text-sm font-black text-slate-950 dark:text-white">
                                            Правильный ответ
                                        </p>

                                        <div class="text-sm font-semibold leading-7 text-emerald-700 dark:text-emerald-200">
                                            @forelse($correctAnswers as $answer)
                                                <p>• {{ $answer->answer_text }}</p>
                                            @empty
                                                <p class="text-slate-500 dark:text-slate-400">Правильный ответ не задан</p>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4 rounded-[1.4rem] border border-slate-200 bg-white p-4 text-sm font-semibold text-slate-500 dark:border-white/10 dark:bg-white/[0.03] dark:text-slate-400">
                                    Баллы:
                                    <span class="font-black text-slate-950 dark:text-white">
                                        {{ $studentAnswer->points_awarded }} из {{ $question->points }}
                                    </span>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>

                <div class="mt-8">
                    <a href="{{ route('tests.show', $attempt->test) }}"
                       class="inline-flex items-center justify-center rounded-2xl border border-slate-200 px-5 py-3 text-sm font-black text-slate-700 transition hover:border-blue-400 hover:text-blue-600 dark:border-white/10 dark:text-slate-200">
                        Назад к тесту
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>