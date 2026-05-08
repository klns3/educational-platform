<x-app-layout>
    <div class="min-h-screen bg-[#f6f8fc] text-slate-950 transition-colors duration-300 dark:bg-[#07111f] dark:text-white">
        <div class="relative overflow-hidden">
            <div class="pointer-events-none absolute inset-0 hidden dark:block">
                <div class="absolute left-[16%] top-0 h-80 w-80 rounded-full bg-blue-600/20 blur-3xl"></div>
                <div class="absolute right-0 top-10 h-96 w-96 rounded-full bg-violet-600/20 blur-3xl"></div>
                <div class="absolute bottom-0 left-1/2 h-72 w-72 rounded-full bg-cyan-500/10 blur-3xl"></div>
            </div>

            <div class="relative mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
                <section class="mb-7">
                    <p class="mb-4 inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700 dark:border-blue-400/20 dark:bg-blue-500/10 dark:text-blue-200">
                        Результаты
                    </p>

                    <h1 class="text-3xl font-black tracking-tight text-slate-950 sm:text-4xl dark:text-white">
                        {{ $pageTitle }}
                    </h1>

                    <p class="mt-3 max-w-3xl text-sm font-semibold text-slate-500 sm:text-base dark:text-slate-300">
                        {{ $pageDescription }}
                    </p>
                </section>

                <section class="mb-6 overflow-hidden rounded-[1.7rem] border border-white bg-white p-4 shadow-sm shadow-slate-200/70 dark:border-white/10 dark:bg-white/[0.04] dark:shadow-none">
                    <form method="GET" action="{{ route('results.my') }}" class="flex flex-col gap-3 sm:flex-row sm:items-center">
                        <input type="text"
                               name="q"
                               value="{{ $search }}"
                               placeholder="Поиск по тесту, курсу{{ $showAuthor ? ' или автору' : '' }}"
                               class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-blue-400 focus:ring-2 focus:ring-blue-100 dark:border-white/10 dark:bg-white/[0.05] dark:text-slate-100 dark:placeholder:text-slate-400 dark:focus:border-blue-400 dark:focus:ring-blue-500/10">

                        <div class="flex shrink-0 gap-3">
                            <button type="submit"
                                    class="inline-flex items-center justify-center rounded-2xl bg-blue-600 px-5 py-3 text-sm font-black text-white shadow-md shadow-blue-600/20 transition hover:-translate-y-0.5 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-400">
                                Найти
                            </button>

                            @if($search !== '')
                                <a href="{{ route('results.my') }}"
                                   class="inline-flex items-center justify-center rounded-2xl border border-slate-200 px-5 py-3 text-sm font-black text-slate-700 transition hover:border-blue-400 hover:text-blue-600 dark:border-white/10 dark:text-slate-200">
                                    Сбросить
                                </a>
                            @endif
                        </div>
                    </form>
                </section>

                <section class="overflow-hidden rounded-[1.7rem] border border-white bg-white shadow-sm shadow-slate-200/70 dark:border-white/10 dark:bg-white/[0.04] dark:shadow-none">
                    <div class="grid divide-y divide-slate-100 dark:divide-white/10">
                        @forelse($tests as $test)
                            @php
                                $averageScore = $test->student_average_score !== null
                                    ? round((float) $test->student_average_score, 2)
                                    : null;
                            @endphp

                            <article class="p-5 transition hover:bg-slate-50 dark:hover:bg-white/[0.03]">
                                <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-black text-blue-600 dark:bg-blue-500/15 dark:text-blue-300">
                                                {{ $test->student_attempts_count }} попыток
                                            </span>

                                            <span class="rounded-full bg-slate-50 px-3 py-1 text-xs font-black text-slate-600 dark:bg-white/10 dark:text-slate-300">
                                                {{ $test->course->title }}
                                            </span>
                                        </div>

                                        <h2 class="mt-3 text-xl font-black text-slate-950 dark:text-white">
                                            {{ $test->title }}
                                        </h2>

                                        <p class="mt-2 text-sm font-semibold text-slate-500 dark:text-slate-400">
                                            Средний балл:
                                            <span class="font-black text-slate-900 dark:text-slate-100">
                                                {{ $averageScore !== null ? $averageScore : 'нет данных' }}
                                            </span>
                                        </p>

                                        <p class="mt-1 text-sm font-semibold text-slate-500 dark:text-slate-400">
                                            Статус:
                                            <span class="font-black text-slate-900 dark:text-slate-100">
                                                {{ $test->is_published ? 'опубликован' : 'черновик' }}
                                            </span>
                                        </p>

                                        @if($showAuthor)
                                            <p class="mt-1 text-sm font-semibold text-slate-500 dark:text-slate-400">
                                                Автор:
                                                <span class="font-black text-slate-900 dark:text-slate-100">
                                                    {{ $test->author->name ?? 'не указан' }}
                                                </span>
                                            </p>
                                        @endif
                                    </div>

                                    <div class="shrink-0">
                                        <a href="{{ route('results.test', $test) }}"
                                           class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-4 py-2 text-sm font-black text-white shadow-md shadow-blue-600/20 transition hover:-translate-y-0.5 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-400">
                                            Открыть результаты
                                        </a>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <div class="px-5 py-12 text-center">
                                <div class="mx-auto mb-5 flex h-20 w-20 items-center justify-center rounded-[1.7rem] bg-blue-50 text-4xl dark:bg-blue-500/15">
                                    📊
                                </div>

                                <h2 class="text-2xl font-black text-slate-950 dark:text-white">
                                    {{ $search !== '' ? 'Ничего не найдено' : $emptyTitle }}
                                </h2>

                                <p class="mt-2 text-sm font-semibold text-slate-500 dark:text-slate-400">
                                    {{ $search !== '' ? 'Попробуйте изменить поисковый запрос.' : $emptyDescription }}
                                </p>
                            </div>
                        @endforelse
                    </div>
                </section>
            </div>
        </div>
    </div>
</x-app-layout>
