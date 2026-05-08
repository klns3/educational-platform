<x-app-layout>
    @php
        $averageScoreValue = round((float) $averageScore, 2);
        $bestScoreValue = round((float) $bestScore, 2);

        $resultStatus = function (int|float $percent) {
            if ($percent >= 85) {
                return ['text' => 'Отлично', 'class' => 'text-emerald-500', 'bar' => 'bg-emerald-500'];
            }

            if ($percent >= 70) {
                return ['text' => 'Хорошо', 'class' => 'text-amber-500', 'bar' => 'bg-amber-500'];
            }

            return ['text' => 'Можно лучше', 'class' => 'text-orange-500', 'bar' => 'bg-orange-500'];
        };

        $testIcons = ['√x', 'π', '∑', 'ƒ', 'x²'];
    @endphp

    <div class="min-h-screen bg-[#f6f8fc] text-slate-950 transition-colors duration-300 dark:bg-[#07111f] dark:text-white">
        <div class="relative overflow-hidden">
            <div class="pointer-events-none absolute inset-0 hidden dark:block">
                <div class="absolute left-[18%] top-0 h-80 w-80 rounded-full bg-blue-600/20 blur-3xl"></div>
                <div class="absolute right-0 top-10 h-96 w-96 rounded-full bg-violet-600/20 blur-3xl"></div>
                <div class="absolute bottom-0 left-1/2 h-72 w-72 rounded-full bg-cyan-500/10 blur-3xl"></div>
            </div>

            <div class="relative mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
                <section class="mb-7 grid gap-6 lg:grid-cols-[1fr_310px]">
                    <div>
                        <p class="mb-4 inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700 dark:border-blue-400/20 dark:bg-blue-500/10 dark:text-blue-200">
                            Кабинет ученика
                        </p>

                        <h1 class="text-3xl font-black tracking-tight text-slate-950 sm:text-4xl dark:text-white">
                            Добро пожаловать, {{ Auth::user()->name }}! 👋
                        </h1>

                        <p class="mt-3 max-w-2xl text-sm font-semibold text-slate-500 sm:text-base dark:text-slate-300">
                            Продолжай учиться, проходить тесты и собирать сильные результаты. Без воды — только прогресс.
                        </p>
                    </div>


                </section>

                <section class="mb-6 grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
                    <div class="group rounded-[1.7rem] border border-white bg-white p-5 shadow-sm shadow-slate-200/70 transition hover:-translate-y-1 hover:shadow-xl hover:shadow-blue-100 dark:border-white/10 dark:bg-white/[0.04] dark:shadow-none">
                        <div class="flex items-center gap-4">
                            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-50 text-2xl text-blue-600 shadow-inner dark:bg-blue-500/15 dark:text-blue-300">
                                📘
                            </div>
                            <div>
                                <p class="text-sm font-bold text-slate-500 dark:text-slate-400">Доступных тестов</p>
                                <p class="mt-1 text-4xl font-black text-slate-950 dark:text-white">{{ $availableTestsCount }}</p>
                                <p class="mt-1 text-xs font-black text-blue-600 dark:text-blue-300">В твоих курсах</p>
                            </div>
                        </div>
                    </div>

                    <div class="group rounded-[1.7rem] border border-white bg-white p-5 shadow-sm shadow-slate-200/70 transition hover:-translate-y-1 hover:shadow-xl hover:shadow-emerald-100 dark:border-white/10 dark:bg-white/[0.04] dark:shadow-none">
                        <div class="flex items-center gap-4">
                            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-50 text-2xl text-emerald-600 shadow-inner dark:bg-emerald-500/15 dark:text-emerald-300">
                                📈
                            </div>
                            <div>
                                <p class="text-sm font-bold text-slate-500 dark:text-slate-400">Моих попыток</p>
                                <p class="mt-1 text-4xl font-black text-slate-950 dark:text-white">{{ $attemptsCount }}</p>
                                <p class="mt-1 text-xs font-black text-emerald-600 dark:text-emerald-300">Завершённых прохождений</p>
                            </div>
                        </div>
                    </div>

                    <div class="group rounded-[1.7rem] border border-white bg-white p-5 shadow-sm shadow-slate-200/70 transition hover:-translate-y-1 hover:shadow-xl hover:shadow-violet-100 dark:border-white/10 dark:bg-white/[0.04] dark:shadow-none">
                        <div class="flex items-center gap-4">
                            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-violet-50 text-2xl text-violet-600 shadow-inner dark:bg-violet-500/15 dark:text-violet-300">
                                🏅
                            </div>
                            <div>
                                <p class="text-sm font-bold text-slate-500 dark:text-slate-400">Средний балл</p>
                                <p class="mt-1 text-4xl font-black text-slate-950 dark:text-white">{{ $averageScoreValue }}</p>
                                <p class="mt-1 text-xs font-black text-violet-600 dark:text-violet-300">По всем попыткам</p>
                            </div>
                        </div>
                    </div>

                    <div class="group rounded-[1.7rem] border border-white bg-white p-5 shadow-sm shadow-slate-200/70 transition hover:-translate-y-1 hover:shadow-xl hover:shadow-orange-100 dark:border-white/10 dark:bg-white/[0.04] dark:shadow-none">
                        <div class="flex items-center gap-4">
                            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-orange-50 text-2xl text-orange-600 shadow-inner dark:bg-orange-500/15 dark:text-orange-300">
                                🏆
                            </div>
                            <div>
                                <p class="text-sm font-bold text-slate-500 dark:text-slate-400">Лучший балл</p>
                                <p class="mt-1 text-4xl font-black text-slate-950 dark:text-white">{{ $bestScoreValue }}</p>
                                <p class="mt-1 text-xs font-black text-orange-600 dark:text-orange-300">Личный рекорд</p>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="grid grid-cols-1 gap-6 xl:grid-cols-12">
                    <div class="xl:col-span-6">
                        <div class="h-full overflow-hidden rounded-[1.7rem] border border-white bg-white shadow-sm shadow-slate-200/70 dark:border-white/10 dark:bg-white/[0.04] dark:shadow-none">
                            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5 dark:border-white/10">
                                <h2 class="text-xl font-black text-slate-950 dark:text-white">Последние результаты</h2>
                                <a href="{{ route('results.my') }}" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-black text-slate-500 transition hover:border-blue-400 hover:text-blue-600 dark:border-white/10 dark:text-slate-300">
                                    Все результаты
                                </a>
                            </div>

                            <div class="p-5">
                                @forelse($latestAttempts as $attempt)
                                    @php
                                        $percent = $attempt->max_score > 0 ? round(($attempt->score / $attempt->max_score) * 100) : 0;
                                        $status = $resultStatus($percent);
                                    @endphp

                                    <div class="relative flex gap-4 border-l-2 border-slate-200 pb-5 pl-6 last:pb-0 dark:border-white/10">
                                        <div class="absolute -left-[9px] top-5 h-4 w-4 rounded-full border-2 border-blue-400 bg-white dark:bg-[#07111f]"></div>

                                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-blue-50 text-lg font-black text-blue-600 dark:bg-blue-500/20 dark:text-blue-300">
                                            {{ $testIcons[$loop->index % count($testIcons)] }}
                                        </div>

                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-start justify-between gap-4">
                                                <div class="min-w-0">
                                                    <p class="truncate font-black text-slate-950 dark:text-white">
                                                        {{ $attempt->test?->title ?? 'Тест удалён' }}
                                                    </p>
                                                    <p class="mt-1 text-sm font-semibold text-slate-500 dark:text-slate-400">
                                                        {{ $attempt->created_at->format('d.m.Y') }} • {{ $attempt->created_at->format('H:i') }}
                                                    </p>
                                                </div>

                                                <div class="shrink-0 text-right">
                                                    <p class="text-xl font-black {{ $status['class'] }}">{{ $percent }}%</p>
                                                    <p class="text-xs font-black {{ $status['class'] }}">{{ $status['text'] }}</p>
                                                </div>
                                            </div>

                                            <div class="mt-3 h-2 overflow-hidden rounded-full bg-slate-100 dark:bg-white/5">
                                                <div class="h-full rounded-full {{ $status['bar'] }}" style="width: {{ min(100, max(0, $percent)) }}%"></div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center dark:border-white/10 dark:bg-white/[0.03]">
                                        <p class="font-black text-slate-950 dark:text-white">Результатов пока нет</p>
                                        <p class="mt-2 text-sm font-semibold text-slate-500 dark:text-slate-400">
                                            Пройди первый тест, и здесь появится история.
                                        </p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div class="xl:col-span-6">
                        <div class="h-full overflow-hidden rounded-[1.7rem] border border-white bg-white shadow-sm shadow-slate-200/70 dark:border-white/10 dark:bg-white/[0.04] dark:shadow-none">
                            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5 dark:border-white/10">
                                <h2 class="text-xl font-black text-slate-950 dark:text-white">Доступные тесты</h2>
                                <a href="{{ route('courses.index') }}" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-black text-slate-500 transition hover:border-blue-400 hover:text-blue-600 dark:border-white/10 dark:text-slate-300">
                                    Все курсы
                                </a>
                            </div>

                            <div class="divide-y divide-slate-100 p-5 dark:divide-white/10">
                                @forelse($latestTests as $test)
                                    @php
                                        $accentClasses = match($loop->index % 4) {
                                            0 => 'bg-blue-50 text-blue-600 dark:bg-blue-500/20 dark:text-blue-300',
                                            1 => 'bg-violet-50 text-violet-600 dark:bg-violet-500/20 dark:text-violet-300',
                                            2 => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-300',
                                            default => 'bg-orange-50 text-orange-600 dark:bg-orange-500/20 dark:text-orange-300',
                                        };

                                        $usedAttempts = $testAttemptCounts[$test->id] ?? 0;
                                        $bestPercent = $bestPercentByTest[$test->id] ?? null;
                                    @endphp

                                    <div class="flex flex-col gap-4 py-5 first:pt-0 last:pb-0 sm:flex-row sm:items-center sm:justify-between">
                                        <div class="flex min-w-0 items-center gap-4">
                                            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl text-xl font-black {{ $accentClasses }}">
                                                {{ $testIcons[$loop->index % count($testIcons)] }}
                                            </div>

                                            <div class="min-w-0">
                                                <p class="truncate font-black text-slate-950 dark:text-white">{{ $test->title }}</p>
                                                <p class="mt-1 truncate text-sm font-semibold text-slate-500 dark:text-slate-400">
                                                    Курс: {{ $test->course?->title ?? 'Без курса' }}
                                                </p>
                                            </div>
                                        </div>

                                        <div class="flex flex-wrap items-center gap-4 sm:justify-end">
                                            <div class="text-sm font-bold text-slate-500 dark:text-slate-400">
                                                ⏱ {{ $test->time_limit ? $test->time_limit . ' мин.' : 'Без лимита' }}
                                            </div>

                                            <div class="text-sm font-bold text-slate-500 dark:text-slate-400">
                                                Попыток: {{ $usedAttempts }}{{ $test->attempts_limit ? ' / ' . $test->attempts_limit : '' }}
                                            </div>

                                            @if($bestPercent !== null)
                                                <div class="text-sm font-black text-emerald-500">
                                                    Лучший: {{ $bestPercent }}%
                                                </div>
                                            @endif

                                            <a href="{{ route('tests.take', $test) }}" class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-5 py-3 text-sm font-black text-white shadow-lg shadow-blue-600/20 transition hover:-translate-y-0.5 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-400">
                                                Начать тест
                                            </a>
                                        </div>
                                    </div>
                                @empty
                                    <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center dark:border-white/10 dark:bg-white/[0.03]">
                                        <p class="font-black text-slate-950 dark:text-white">Доступных тестов пока нет</p>
                                        <p class="mt-2 text-sm font-semibold text-slate-500 dark:text-slate-400">
                                            Когда преподаватель опубликует тесты, они появятся здесь.
                                        </p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </section>

                <section class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
                    <div class="rounded-[1.7rem] border border-white bg-white p-6 shadow-sm shadow-slate-200/70 dark:border-white/10 dark:bg-white/[0.04] dark:shadow-none">
                        <h2 class="text-lg font-black text-slate-950 dark:text-white">🔥 Серия успеха</h2>
                        <p class="mt-4 text-4xl font-black text-slate-950 dark:text-white">{{ $currentStreak }} дн.</p>
                        <p class="mt-1 text-sm font-semibold text-slate-500 dark:text-slate-400">
                            Дни с активностью подряд.
                        </p>
                    </div>

                    <div class="rounded-[1.7rem] border border-white bg-white p-6 shadow-sm shadow-slate-200/70 dark:border-white/10 dark:bg-white/[0.04] dark:shadow-none">
                        <h2 class="text-lg font-black text-slate-950 dark:text-white">📊 Прогресс обучения</h2>
                        <p class="mt-4 text-4xl font-black text-slate-950 dark:text-white">{{ $progressPercent }}%</p>
                        <p class="mt-1 text-sm font-semibold text-slate-500 dark:text-slate-400">
                            Пройдено {{ $completedTestsCount }} из {{ $availableTestsCount }} доступных тестов.
                        </p>

                        <div class="mt-5 h-3 overflow-hidden rounded-full bg-slate-100 dark:bg-white/5">
                            <div class="h-full rounded-full bg-blue-600 dark:bg-blue-400" style="width: {{ min(100, max(0, $progressPercent)) }}%"></div>
                        </div>
                    </div>

                    <div class="rounded-[1.7rem] border border-white bg-white p-6 shadow-sm shadow-slate-200/70 dark:border-white/10 dark:bg-white/[0.04] dark:shadow-none">
                        <h2 class="text-lg font-black text-slate-950 dark:text-white">🎯 Быстрые действия</h2>

                        <div class="mt-5 grid gap-3">
                            <a href="{{ route('schedule.index') }}" class="rounded-2xl border border-slate-200 px-4 py-3 text-sm font-black text-slate-700 transition hover:border-blue-400 hover:text-blue-600 dark:border-white/10 dark:text-slate-200">
                                Открыть расписание
                            </a>

                            <a href="{{ route('support-tickets.index') }}" class="rounded-2xl border border-slate-200 px-4 py-3 text-sm font-black text-slate-700 transition hover:border-blue-400 hover:text-blue-600 dark:border-white/10 dark:text-slate-200">
                                Мои заявки: {{ $openTicketsCount }}
                            </a>
                        </div>
                    </div>
                </section>

                <section class="mt-6 rounded-[1.7rem] border border-white bg-white p-6 shadow-sm shadow-slate-200/70 dark:border-white/10 dark:bg-white/[0.04] dark:shadow-none">
                    <div class="mb-5 flex items-center justify-between">
                        <h2 class="text-xl font-black text-slate-950 dark:text-white">Ближайшее расписание</h2>
                        <a href="{{ route('schedule.index') }}" class="text-sm font-black text-blue-600 dark:text-blue-300">
                            Всё расписание →
                        </a>
                    </div>

                    <div class="grid gap-4 md:grid-cols-3">
                        @forelse($upcomingEvents as $event)
                            <div class="rounded-2xl bg-slate-50 p-4 dark:bg-white/[0.04]">
                                <p class="text-sm font-black text-blue-600 dark:text-blue-300">
                                    {{ $event->starts_at->format('d.m.Y H:i') }}
                                </p>
                                <p class="mt-2 font-black text-slate-950 dark:text-white">
                                    {{ $event->title ?? $event->course?->title ?? 'Занятие' }}
                                </p>
                                <p class="mt-1 text-sm font-semibold text-slate-500 dark:text-slate-400">
                                    {{ $event->location ?: 'Место не указано' }}
                                </p>
                            </div>
                        @empty
                            <div class="md:col-span-3 rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center dark:border-white/10 dark:bg-white/[0.03]">
                                <p class="font-black text-slate-950 dark:text-white">Ближайших занятий пока нет</p>
                                <p class="mt-2 text-sm font-semibold text-slate-500 dark:text-slate-400">
                                    Когда преподаватель добавит расписание для твоей группы, оно появится здесь.
                                </p>
                            </div>
                        @endforelse
                    </div>
                </section>
            </div>
        </div>
    </div>
</x-app-layout>