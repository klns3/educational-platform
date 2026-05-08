<x-app-layout>
    <div class="min-h-screen bg-[#f6f8fc] text-slate-950 transition-colors duration-300 dark:bg-[#07111f] dark:text-white">
        <div class="relative overflow-hidden">
            <div class="pointer-events-none absolute inset-0 hidden dark:block">
                <div class="absolute left-[16%] top-0 h-80 w-80 rounded-full bg-blue-600/20 blur-3xl"></div>
                <div class="absolute right-0 top-10 h-96 w-96 rounded-full bg-violet-600/20 blur-3xl"></div>
                <div class="absolute bottom-0 left-1/2 h-72 w-72 rounded-full bg-cyan-500/10 blur-3xl"></div>
            </div>

            <div class="relative mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
                <section class="mb-7">
                    <p class="mb-4 inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700 dark:border-blue-400/20 dark:bg-blue-500/10 dark:text-blue-200">
                        Прохождение теста
                    </p>

                    <h1 class="text-3xl font-black tracking-tight text-slate-950 sm:text-4xl dark:text-white">
                        {{ $test->title }}
                    </h1>
                </section>

                @if(session('error'))
                    <div class="mb-6 rounded-[1.4rem] border border-red-200 bg-red-50 px-5 py-4 text-sm font-black text-red-700 dark:border-red-400/20 dark:bg-red-500/10 dark:text-red-200">
                        {{ session('error') }}
                    </div>
                @endif

                @if($test->time_limit)
                    <div class="mb-6 rounded-[1.4rem] border border-amber-200 bg-amber-50 px-5 py-4 dark:border-amber-400/20 dark:bg-amber-500/10">
                        <div class="flex items-center justify-between gap-4">
                            <span class="text-sm font-black text-amber-700 dark:text-amber-200">
                                Оставшееся время
                            </span>
                            <span id="timer" class="text-xl font-black text-red-600 dark:text-red-300"></span>
                        </div>
                    </div>
                @endif

                <form id="test-form"
                      action="{{ route('tests.submit', $test) }}"
                      method="POST"
                      class="overflow-hidden rounded-[1.7rem] border border-white bg-white shadow-sm shadow-slate-200/70 dark:border-white/10 dark:bg-white/[0.04] dark:shadow-none">
                    @csrf

                    <div class="grid divide-y divide-slate-100 dark:divide-white/10">
                        @foreach($test->questions as $question)
                            <article class="p-6">
                                <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
                                    <h2 class="text-lg font-black leading-7 text-slate-950 dark:text-white">
                                        {{ $loop->iteration }}. {{ $question->question_text }}
                                    </h2>

                                    <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-black text-blue-600 dark:bg-blue-500/15 dark:text-blue-300">
                                        {{ $question->points }} балл.
                                    </span>
                                </div>

                                <div class="grid gap-3">
                                    @if($question->question_type === 'single')
                                        @foreach($question->answers as $answer)
                                            <label class="flex items-center gap-3 rounded-[1.2rem] border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-blue-400 hover:bg-blue-50 dark:border-white/10 dark:bg-white/[0.03] dark:text-slate-200 dark:hover:bg-blue-500/10">
                                                <input type="radio"
                                                       name="answers[{{ $question->id }}]"
                                                       value="{{ $answer->id }}"
                                                       class="border-slate-300 text-blue-600 focus:ring-blue-500">
                                                <span>{{ $answer->answer_text }}</span>
                                            </label>
                                        @endforeach
                                    @endif

                                    @if($question->question_type === 'multiple')
                                        @foreach($question->answers as $answer)
                                            <label class="flex items-center gap-3 rounded-[1.2rem] border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-blue-400 hover:bg-blue-50 dark:border-white/10 dark:bg-white/[0.03] dark:text-slate-200 dark:hover:bg-blue-500/10">
                                                <input type="checkbox"
                                                       name="answers[{{ $question->id }}][]"
                                                       value="{{ $answer->id }}"
                                                       class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                                <span>{{ $answer->answer_text }}</span>
                                            </label>
                                        @endforeach
                                    @endif

                                    @if($question->question_type === 'text')
                                        <input type="text"
                                               name="answers[{{ $question->id }}]"
                                               class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-950 outline-none transition placeholder:text-slate-400 focus:border-blue-400 focus:bg-white focus:ring-4 focus:ring-blue-500/10 dark:border-white/10 dark:bg-white/[0.04] dark:text-white"
                                               placeholder="Введите ответ">
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>

                    <div class="flex justify-end border-t border-slate-100 px-6 py-5 dark:border-white/10">
                        <button type="submit"
                                class="inline-flex items-center justify-center rounded-2xl bg-blue-600 px-6 py-3 text-sm font-black text-white shadow-lg shadow-blue-600/20 transition hover:-translate-y-0.5 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-400">
                            Завершить тест
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if($test->time_limit)
        <script>
            let timeLimit = Math.ceil(Number({{ $remainingSeconds ?? 0 }}));
            let timerEl = document.getElementById('timer');
            let testForm = document.getElementById('test-form');

            function formatTime(seconds) {
                seconds = Math.max(0, Math.floor(seconds));

                let m = Math.floor(seconds / 60);
                let s = seconds % 60;

                return `${m}:${s < 10 ? '0' : ''}${s}`;
            }

            function updateTimer() {
                timerEl.innerText = formatTime(timeLimit);

                if (timeLimit <= 0) {
                    clearInterval(timerInterval);

                    testForm.submit();

                    document.body.innerHTML = `
                        <div style="display:flex;justify-content:center;align-items:center;height:100vh;font-size:24px;">
                            Время вышло. Отправка теста...
                        </div>
                    `;

                    return;
                }

                timeLimit--;
            }

            updateTimer();

            let timerInterval = setInterval(updateTimer, 1000);
        </script>
    @endif
</x-app-layout>