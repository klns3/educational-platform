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
                        Вопросы
                    </p>

                    <h1 class="text-3xl font-black tracking-tight text-slate-950 sm:text-4xl dark:text-white">
                        Редактировать вопрос
                    </h1>

                    <p class="mt-3 max-w-2xl text-sm font-semibold text-slate-500 sm:text-base dark:text-slate-300">
                        Изменение текста, типа вопроса, баллов и вариантов ответа.
                    </p>
                </section>

                <form action="{{ route('questions.update', $question) }}"
                      method="POST"
                      class="overflow-hidden rounded-[1.7rem] border border-white bg-white shadow-sm shadow-slate-200/70 dark:border-white/10 dark:bg-white/[0.04] dark:shadow-none">
                    @csrf
                    @method('PUT')

                    <div class="border-b border-slate-100 px-6 py-5 dark:border-white/10">
                        <h2 class="text-xl font-black text-slate-950 dark:text-white">
                            Основная информация
                        </h2>
                    </div>

                    <div class="grid gap-6 p-6">
                        <div>
                            <label class="mb-2 block text-sm font-black text-slate-700 dark:text-slate-200">
                                Текст вопроса
                            </label>

                            <textarea name="question_text"
                                      rows="4"
                                      class="w-full resize-none rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-950 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-4 focus:ring-blue-500/10 dark:border-white/10 dark:bg-white/[0.04] dark:text-white"
                                      required>{{ old('question_text', $question->question_text) }}</textarea>
                        </div>

                        <div class="grid gap-5 md:grid-cols-2">
                            <div>
                                <label class="mb-2 block text-sm font-black text-slate-700 dark:text-slate-200">
                                    Тип вопроса
                                </label>

                                <select name="question_type"
                                        id="question_type"
                                        class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-950 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-4 focus:ring-blue-500/10 dark:border-white/10 dark:bg-white/[0.04] dark:text-white">
                                    <option value="single" @selected($question->question_type === 'single')>Один правильный ответ</option>
                                    <option value="multiple" @selected($question->question_type === 'multiple')>Несколько правильных ответов</option>
                                    <option value="text" @selected($question->question_type === 'text')>Текстовый ответ</option>
                                </select>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-black text-slate-700 dark:text-slate-200">
                                    Баллы
                                </label>

                                <input type="number"
                                       name="points"
                                       value="{{ old('points', $question->points) }}"
                                       min="1"
                                       class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-950 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-4 focus:ring-blue-500/10 dark:border-white/10 dark:bg-white/[0.04] dark:text-white"
                                       required>
                            </div>
                        </div>

                        <div id="answers_block" class="rounded-[1.4rem] border border-slate-200 bg-slate-50 p-5 dark:border-white/10 dark:bg-white/[0.03]">
                            <div class="mb-4 flex items-center justify-between gap-4">
                                <div>
                                    <h2 class="text-lg font-black text-slate-950 dark:text-white" id="answers_title">
                                        Варианты ответов
                                    </h2>

                                    <p class="mt-1 text-sm font-semibold text-slate-500 dark:text-slate-400" id="answers_help">
                                        Для обычных вопросов отметьте правильный вариант. Для текстового вопроса укажите эталонный ответ.
                                    </p>
                                </div>

                                <button type="button"
                                        id="add_answer_btn"
                                        onclick="addAnswer()"
                                        class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-emerald-200 bg-emerald-50 text-lg font-black text-emerald-600 transition hover:border-emerald-400 hover:bg-emerald-100 dark:border-emerald-400/20 dark:bg-emerald-500/10 dark:text-emerald-300">
                                    +
                                </button>
                            </div>

                            <div id="answers_container" class="grid gap-3"></div>
                        </div>
                    </div>

                    <div class="flex flex-col gap-3 border-t border-slate-100 px-6 py-5 sm:flex-row sm:items-center sm:justify-between dark:border-white/10">
                        <a href="{{ route('questions.index', $question->test) }}"
                           class="inline-flex items-center justify-center rounded-2xl border border-slate-200 px-5 py-3 text-sm font-black text-slate-700 transition hover:border-blue-400 hover:text-blue-600 dark:border-white/10 dark:text-slate-200">
                            Отмена
                        </a>

                        <button type="submit"
                                class="inline-flex items-center justify-center rounded-2xl bg-blue-600 px-6 py-3 text-sm font-black text-white shadow-lg shadow-blue-600/20 transition hover:-translate-y-0.5 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-400">
                            Обновить
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        let answerIndex = 0;

        function getQuestionType() {
            return document.getElementById('question_type').value;
        }

        function addAnswer(value = '', checked = false) {
            const container = document.getElementById('answers_container');
            const type = getQuestionType();

            if (type === 'text' && container.querySelectorAll('.answer-row').length >= 1) {
                return;
            }

            const div = document.createElement('div');
            div.className = 'answer-row flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-4 sm:flex-row sm:items-center dark:border-white/10 dark:bg-white/[0.04]';

            const correctInputHtml = type === 'text'
                ? `<input type="hidden" name="correct_answer" value="${answerIndex}">`
                : `
                    <label class="flex shrink-0 items-center gap-2 rounded-xl border border-slate-200 px-3 py-2 text-xs font-black text-slate-600 dark:border-white/10 dark:text-slate-300">
                        <input type="${type === 'single' ? 'radio' : 'checkbox'}"
                               name="${type === 'single' ? 'correct_answer' : 'correct_answers[]'}"
                               value="${answerIndex}"
                               class="rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                               ${checked ? 'checked' : ''}>
                        Верный
                    </label>
                `;

            const removeButtonHtml = type === 'text'
                ? ''
                : `
                    <button type="button"
                            onclick="this.closest('.answer-row').remove()"
                            class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-red-200 bg-red-50 text-lg font-black text-red-600 transition hover:border-red-400 hover:bg-red-100 dark:border-red-400/20 dark:bg-red-500/10 dark:text-red-300">
                        −
                    </button>
                `;

            div.innerHTML = `
                <input type="text"
                       name="answers[${answerIndex}]"
                       value="${value}"
                       placeholder="${type === 'text' ? 'Эталонный ответ' : 'Вариант ответа'}"
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-950 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-4 focus:ring-blue-500/10 dark:border-white/10 dark:bg-white/[0.04] dark:text-white"
                       ${type === 'text' ? 'required' : ''}>

                ${correctInputHtml}
                ${removeButtonHtml}
            `;

            container.appendChild(div);
            answerIndex++;
        }

        function rebuildCorrectInputs(keepValues = true) {
            const type = getQuestionType();
            const container = document.getElementById('answers_container');

            document.getElementById('answers_title').innerText =
                type === 'text' ? 'Эталонный ответ' : 'Варианты ответов';

            document.getElementById('answers_help').innerText =
                type === 'text'
                    ? 'Введите правильный текстовый ответ. Ответ студента будет сравниваться с этим значением.'
                    : 'Добавьте варианты и отметьте правильный ответ.';

            document.getElementById('add_answer_btn').style.display =
                type === 'text' ? 'none' : 'inline-flex';

            if (!keepValues) {
                return;
            }

            const values = Array.from(container.querySelectorAll('input[type="text"]'))
                .map(input => input.value);

            container.innerHTML = '';
            answerIndex = 0;

            if (type === 'text') {
                addAnswer(values[0] ?? '', true);
                return;
            }

            if (values.length > 0) {
                values.forEach(value => addAnswer(value, false));
            } else {
                addAnswer();
                addAnswer();
            }
        }

        document.getElementById('question_type').addEventListener('change', () => {
            rebuildCorrectInputs(true);
        });

        @foreach($question->answers as $answer)
            addAnswer(@json($answer->answer_text), {{ $answer->is_correct ? 'true' : 'false' }});
        @endforeach

        if (answerIndex === 0) {
            addAnswer();
            addAnswer();
        }

        rebuildCorrectInputs(false);
    </script>
</x-app-layout>