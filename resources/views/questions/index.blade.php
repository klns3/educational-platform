<x-app-layout>
    <div class="min-h-screen bg-[#f6f8fc] text-slate-950 transition-colors duration-300 dark:bg-[#07111f] dark:text-white">
        <div class="relative overflow-hidden">
            <div class="pointer-events-none absolute inset-0 hidden dark:block">
                <div class="absolute left-[16%] top-0 h-80 w-80 rounded-full bg-blue-600/20 blur-3xl"></div>
                <div class="absolute right-0 top-10 h-96 w-96 rounded-full bg-violet-600/20 blur-3xl"></div>
                <div class="absolute bottom-0 left-1/2 h-72 w-72 rounded-full bg-cyan-500/10 blur-3xl"></div>
            </div>

            <div class="relative mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
                <section class="mb-7 flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="mb-4 inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700 dark:border-blue-400/20 dark:bg-blue-500/10 dark:text-blue-200">
                            Вопросы
                        </p>

                        <h1 class="text-3xl font-black tracking-tight text-slate-950 sm:text-4xl dark:text-white">
                            Вопросы теста
                        </h1>

                        <p class="mt-3 max-w-2xl text-sm font-semibold text-slate-500 sm:text-base dark:text-slate-300">
                            {{ $test->title }}
                        </p>
                    </div>

                    @if(in_array(auth()->user()->role, ['admin', 'teacher']))
                        <a href="{{ route('questions.create', $test) }}"
                           class="inline-flex items-center justify-center rounded-2xl bg-blue-600 px-6 py-3 text-sm font-black text-white shadow-lg shadow-blue-600/20 transition hover:-translate-y-0.5 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-400">
                            Добавить вопрос
                        </a>
                    @endif
                </section>

                @if(session('success'))
                    <div class="mb-6 rounded-[1.4rem] border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-black text-emerald-700 dark:border-emerald-400/20 dark:bg-emerald-500/10 dark:text-emerald-200">
                        {{ session('success') }}
                    </div>
                @endif

                <section class="overflow-hidden rounded-[1.7rem] border border-white bg-white shadow-sm shadow-slate-200/70 dark:border-white/10 dark:bg-white/[0.04] dark:shadow-none">
                    <div class="grid divide-y divide-slate-100 dark:divide-white/10">
                        @forelse($questions as $question)
                            @php
                                $questionTypeText = match ($question->question_type) {
                                    'single' => 'Один правильный',
                                    'multiple' => 'Несколько правильных',
                                    default => 'Текстовый',
                                };
                            @endphp

                            <article class="p-6 transition hover:bg-slate-50 dark:hover:bg-white/[0.03]">
                                <div class="mb-4 flex flex-wrap items-center gap-2">
                                    <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-black text-blue-600 dark:bg-blue-500/15 dark:text-blue-300">
                                        {{ $questionTypeText }}
                                    </span>

                                    <span class="rounded-full bg-violet-50 px-3 py-1 text-xs font-black text-violet-600 dark:bg-violet-500/15 dark:text-violet-300">
                                        {{ $question->points }} балл.
                                    </span>
                                </div>

                                <h2 class="text-xl font-black leading-7 text-slate-950 dark:text-white">
                                    {{ $question->question_text }}
                                </h2>

                                @if($question->question_type === 'text')
                                    <div class="mt-5 rounded-[1.4rem] border border-slate-200 bg-slate-50 p-5 dark:border-white/10 dark:bg-white/[0.03]">
                                        <p class="mb-3 text-sm font-black text-slate-950 dark:text-white">
                                            Эталонный ответ
                                        </p>

                                        @php
                                            $textAnswer = $question->answers->where('is_correct', true)->first();
                                        @endphp

                                        @if($textAnswer)
                                            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700 dark:border-emerald-400/20 dark:bg-emerald-500/10 dark:text-emerald-200">
                                                {{ $textAnswer->answer_text }}
                                                <span class="ml-2 font-black">✔ правильный</span>
                                            </div>
                                        @else
                                            <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700 dark:border-red-400/20 dark:bg-red-500/10 dark:text-red-200">
                                                Эталонный ответ не задан
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <div class="mt-5 rounded-[1.4rem] border border-slate-200 bg-slate-50 p-5 dark:border-white/10 dark:bg-white/[0.03]">
                                        <p class="mb-3 text-sm font-black text-slate-950 dark:text-white">
                                            Варианты ответов
                                        </p>

                                        <div class="grid gap-3">
                                            @foreach($question->answers as $answer)
                                                <div class="flex items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-white px-4 py-3 dark:border-white/10 dark:bg-white/[0.04]">
                                                    <span class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                                                        {{ $answer->answer_text }}
                                                    </span>

                                                    @if($answer->is_correct)
                                                        <span class="shrink-0 rounded-full bg-emerald-50 px-3 py-1 text-xs font-black text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-300">
                                                            ✔ правильный
                                                        </span>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                @if(in_array(auth()->user()->role, ['admin', 'teacher']))
                                    <div class="mt-5 flex flex-wrap gap-2">
                                        <a href="{{ route('questions.edit', $question) }}"
                                           class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-4 py-2 text-sm font-black text-slate-700 transition hover:border-blue-400 hover:text-blue-600 dark:border-white/10 dark:text-slate-200">
                                            Редактировать
                                        </a>

                                        <form action="{{ route('questions.destroy', $question) }}"
                                              method="POST"
                                              onsubmit="return confirm('Удалить вопрос?')">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit"
                                                    class="inline-flex items-center justify-center rounded-xl border border-red-200 bg-red-50 px-4 py-2 text-sm font-black text-red-600 transition hover:border-red-400 hover:bg-red-100 dark:border-red-400/20 dark:bg-red-500/10 dark:text-red-300 dark:hover:bg-red-500/15">
                                                Удалить
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </article>
                        @empty
                            <div class="px-5 py-12 text-center">
                                <div class="mx-auto mb-5 flex h-20 w-20 items-center justify-center rounded-[1.7rem] bg-blue-50 text-4xl dark:bg-blue-500/15">
                                    ❔
                                </div>

                                <h2 class="text-2xl font-black text-slate-950 dark:text-white">
                                    Вопросов пока нет
                                </h2>
                            </div>
                        @endforelse
                    </div>
                </section>

                <div class="mt-8">
                    <a href="{{ route('tests.show', $test) }}"
                       class="inline-flex items-center justify-center rounded-2xl border border-slate-200 px-5 py-3 text-sm font-black text-slate-700 transition hover:border-blue-400 hover:text-blue-600 dark:border-white/10 dark:text-slate-200">
                        ← Назад к тесту
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>