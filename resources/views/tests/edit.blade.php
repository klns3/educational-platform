<x-app-layout>
    <div class="min-h-screen bg-[#f6f8fc] text-slate-950 transition-colors duration-300 dark:bg-[#07111f] dark:text-white">
        <div class="relative overflow-hidden">

            <div class="relative mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
                <section class="mb-7">
                    <p class="mb-4 inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700 dark:border-blue-400/20 dark:bg-blue-500/10 dark:text-blue-200">
                        Тесты
                    </p>

                    <h1 class="text-3xl font-black tracking-tight text-slate-950 sm:text-4xl dark:text-white">
                        Редактировать тест
                    </h1>

                    <p class="mt-3 max-w-2xl text-sm font-semibold text-slate-500 sm:text-base dark:text-slate-300">
                        Изменение параметров теста.
                    </p>
                </section>

                <form action="{{ route('tests.update', $test) }}"
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
                                Название
                            </label>

                            <input type="text"
                                   name="title"
                                   value="{{ old('title', $test->title) }}"
                                   class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-950 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-4 focus:ring-blue-500/10 dark:border-white/10 dark:bg-white/[0.04] dark:text-white"
                                   required>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-black text-slate-700 dark:text-slate-200">
                                Описание
                            </label>

                            <textarea name="description"
                                      rows="5"
                                      class="w-full resize-none rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-950 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-4 focus:ring-blue-500/10 dark:border-white/10 dark:bg-white/[0.04] dark:text-white">{{ old('description', $test->description) }}</textarea>
                        </div>

                        <div class="grid gap-5 md:grid-cols-2">
                            <div>
                                <label class="mb-2 block text-sm font-black text-slate-700 dark:text-slate-200">
                                    Ограничение по времени, минут
                                </label>

                                <input type="number"
                                       name="time_limit"
                                       value="{{ old('time_limit', $test->time_limit) }}"
                                       min="1"
                                       class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-950 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-4 focus:ring-blue-500/10 dark:border-white/10 dark:bg-white/[0.04] dark:text-white">
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-black text-slate-700 dark:text-slate-200">
                                    Количество попыток
                                </label>

                                <input type="number"
                                       name="attempts_limit"
                                       value="{{ old('attempts_limit', $test->attempts_limit) }}"
                                       min="1"
                                       class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-950 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-4 focus:ring-blue-500/10 dark:border-white/10 dark:bg-white/[0.04] dark:text-white">
                            </div>
                        </div>

                        <div class="rounded-[1.4rem] border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-400/20 dark:bg-emerald-500/10">
                            <label class="flex items-center justify-between gap-4">
                                <div>
                                    <p class="text-sm font-black text-emerald-700 dark:text-emerald-200">
                                        Опубликовать тест
                                    </p>
                                    <p class="mt-1 text-xs font-semibold text-emerald-700/70 dark:text-emerald-200/70">
                                        После публикации тест станет доступен студентам.
                                    </p>
                                </div>

                                <input type="checkbox"
                                       name="is_published"
                                       class="h-5 w-5 shrink-0 rounded-lg border-emerald-300 bg-white text-emerald-600 focus:ring-emerald-500 dark:border-white/10 dark:bg-slate-900"
                                       @checked($test->is_published)>
                            </label>
                        </div>
                    </div>

                    <div class="flex flex-col gap-3 border-t border-slate-100 px-6 py-5 sm:flex-row sm:items-center sm:justify-between dark:border-white/10">
                        <a href="{{ route('tests.index', $test->course) }}"
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
</x-app-layout>