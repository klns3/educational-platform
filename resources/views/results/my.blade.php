<x-app-layout>
    <div class="min-h-screen bg-[#f6f8fc] text-slate-950 transition-colors duration-300 dark:bg-[#07111f] dark:text-white">
        <div class="relative overflow-hidden">

            <div class="relative mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
                <section class="mb-7">
                    <p class="mb-4 inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700 dark:border-blue-400/20 dark:bg-blue-500/10 dark:text-blue-200">
                        Результаты
                    </p>

                    <h1 class="text-3xl font-black tracking-tight text-slate-950 sm:text-4xl dark:text-white">
                        Мои результаты
                    </h1>

                    <p class="mt-3 max-w-2xl text-sm font-semibold text-slate-500 sm:text-base dark:text-slate-300">
                        История прохождения тестов и набранные баллы.
                    </p>
                </section>

                <section class="overflow-hidden rounded-[1.7rem] border border-white bg-white shadow-sm shadow-slate-200/70 dark:border-white/10 dark:bg-white/[0.04] dark:shadow-none">
                    <div class="grid divide-y divide-slate-100 dark:divide-white/10">
                        @forelse($attempts as $attempt)
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

                            <article class="p-5 transition hover:bg-slate-50 dark:hover:bg-white/[0.03]">
                                <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-black text-blue-600 dark:bg-blue-500/15 dark:text-blue-300">
                                                {{ $percent }}%
                                            </span>

                                            <span class="rounded-full px-3 py-1 text-xs font-black {{ $status['class'] }} bg-slate-50 dark:bg-white/10">
                                                {{ $status['text'] }}
                                            </span>
                                        </div>

                                        <h2 class="mt-3 text-xl font-black text-slate-950 dark:text-white">
                                            {{ $attempt->test->title }}
                                        </h2>

                                        <p class="mt-2 text-sm font-semibold text-slate-500 dark:text-slate-400">
                                            Баллы:
                                            <span class="font-black text-slate-900 dark:text-slate-100">
                                                {{ $attempt->score }} из {{ $attempt->max_score }}
                                            </span>
                                        </p>

                                        <p class="mt-1 text-sm font-semibold text-slate-500 dark:text-slate-400">
                                            Дата прохождения:
                                            <span class="font-black text-slate-900 dark:text-slate-100">
                                                {{ $attempt->finished_at }}
                                            </span>
                                        </p>

                                        <div class="mt-4 h-2 overflow-hidden rounded-full bg-slate-100 dark:bg-white/5">
                                            <div class="h-full rounded-full {{ $status['bar'] }}"
                                                 style="width: {{ min(100, max(0, $percent)) }}%"></div>
                                        </div>
                                    </div>

                                    <div class="shrink-0">
                                        <a href="{{ route('tests.result', $attempt) }}"
                                           class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-4 py-2 text-sm font-black text-white shadow-md shadow-blue-600/20 transition hover:-translate-y-0.5 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-400">
                                            Подробнее
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
                                    Вы ещё не проходили тесты
                                </h2>

                                <p class="mt-2 text-sm font-semibold text-slate-500 dark:text-slate-400">
                                    После прохождения тестов результаты появятся здесь.
                                </p>
                            </div>
                        @endforelse
                    </div>
                </section>
            </div>
        </div>
    </div>
</x-app-layout>