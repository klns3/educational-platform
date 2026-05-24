<x-app-layout>
    @php
        $averageScoreValue = round((float) $averageScore, 2);

        $scoreStatus = function (int|float $percent) {
            if ($percent >= 85) {
                return ['text' => 'Отлично', 'class' => 'text-emerald-500', 'bar' => 'bg-emerald-500'];
            }

            if ($percent >= 70) {
                return ['text' => 'Хорошо', 'class' => 'text-amber-500', 'bar' => 'bg-amber-500'];
            }

            return ['text' => 'Нужно подтянуть', 'class' => 'text-orange-500', 'bar' => 'bg-orange-500'];
        };

        $score = $scoreStatus($averageScoreValue);

        $ticketStatusLabels = [
            'open' => 'Открыта',
            'in_progress' => 'В работе',
            'closed' => 'Закрыта',
        ];

        $eventTypeLabels = [
            'lesson' => 'Занятие',
            'test' => 'Тест',
            'consultation' => 'Консультация',
            'exam' => 'Экзамен',
            'other' => 'Событие',
        ];
    @endphp

    <div class="min-h-screen bg-[#f6f8fc] text-slate-950 transition-colors duration-300 dark:bg-[#07111f] dark:text-white">
        <div class="relative overflow-hidden">

            <div class="relative mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
                <section class="mb-7 grid gap-6 lg:grid-cols-[1fr_330px]">
                    <div>
                        <p class="mb-4 inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700 dark:border-blue-400/20 dark:bg-blue-500/10 dark:text-blue-200">
                            Кабинет преподавателя
                        </p>

                        <h1 class="text-3xl font-black tracking-tight text-slate-950 sm:text-4xl dark:text-white">
                            Добро пожаловать, {{ Auth::user()->name }}!
                        </h1>

                        <p class="mt-3 max-w-2xl text-sm font-semibold text-slate-500 sm:text-base dark:text-slate-300">
                            Курсы, материалы, тесты, активность студентов и ближайшие занятия — всё под рукой.
                        </p>
                    </div>

                    <div class="rounded-[1.7rem] border border-white bg-white p-5 shadow-sm shadow-slate-200/70 dark:border-white/10 dark:bg-white/[0.04] dark:shadow-none">
                        <p class="text-sm font-bold text-slate-500 dark:text-slate-400">Средний результат студентов</p>

                        <div class="mt-3 flex items-end justify-between gap-4">
                            <div>
                                <p class="text-4xl font-black text-slate-950 dark:text-white">{{ $averageScoreValue }}</p>
                                <p class="mt-1 text-xs font-black {{ $score['class'] }}">{{ $score['text'] }}</p>
                            </div>

                            <div class="rounded-2xl bg-blue-50 px-4 py-3 text-2xl dark:bg-blue-500/15">
                                📊
                            </div>
                        </div>

                        <div class="mt-5 h-3 overflow-hidden rounded-full bg-slate-100 dark:bg-white/5">
                            <div class="h-full rounded-full {{ $score['bar'] }}" style="width: {{ min(100, max(0, $averageScoreValue)) }}%"></div>
                        </div>
                    </div>
                </section>

                <section class="mb-6 grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
                    <div class="rounded-[1.7rem] border border-white bg-white p-5 shadow-sm shadow-slate-200/70 transition hover:-translate-y-1 hover:shadow-xl hover:shadow-blue-100 dark:border-white/10 dark:bg-white/[0.04] dark:shadow-none">
                        <div class="flex items-center gap-4">
                            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-50 text-2xl text-blue-600 shadow-inner dark:bg-blue-500/15 dark:text-blue-300">
                                📚
                            </div>
                            <div>
                                <p class="text-sm font-bold text-slate-500 dark:text-slate-400">Мои курсы</p>
                                <p class="mt-1 text-4xl font-black text-slate-950 dark:text-white">{{ $coursesCount }}</p>
                                <p class="mt-1 text-xs font-black text-blue-600 dark:text-blue-300">Учебные направления</p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-[1.7rem] border border-white bg-white p-5 shadow-sm shadow-slate-200/70 transition hover:-translate-y-1 hover:shadow-xl hover:shadow-emerald-100 dark:border-white/10 dark:bg-white/[0.04] dark:shadow-none">
                        <div class="flex items-center gap-4">
                            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-50 text-2xl text-emerald-600 shadow-inner dark:bg-emerald-500/15 dark:text-emerald-300">
                                👥
                            </div>
                            <div>
                                <p class="text-sm font-bold text-slate-500 dark:text-slate-400">Студентов</p>
                                <p class="mt-1 text-4xl font-black text-slate-950 dark:text-white">{{ $assignedStudentsCount }}</p>
                                <p class="mt-1 text-xs font-black text-emerald-600 dark:text-emerald-300">В ваших курсах</p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-[1.7rem] border border-white bg-white p-5 shadow-sm shadow-slate-200/70 transition hover:-translate-y-1 hover:shadow-xl hover:shadow-violet-100 dark:border-white/10 dark:bg-white/[0.04] dark:shadow-none">
                        <div class="flex items-center gap-4">
                            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-violet-50 text-2xl text-violet-600 shadow-inner dark:bg-violet-500/15 dark:text-violet-300">
                                🧪
                            </div>
                            <div>
                                <p class="text-sm font-bold text-slate-500 dark:text-slate-400">Тестов</p>
                                <p class="mt-1 text-4xl font-black text-slate-950 dark:text-white">{{ $testsCount }}</p>
                                <p class="mt-1 text-xs font-black text-violet-600 dark:text-violet-300">Опубликовано: {{ $publishedTestsCount }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-[1.7rem] border border-white bg-white p-5 shadow-sm shadow-slate-200/70 transition hover:-translate-y-1 hover:shadow-xl hover:shadow-orange-100 dark:border-white/10 dark:bg-white/[0.04] dark:shadow-none">
                        <div class="flex items-center gap-4">
                            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-orange-50 text-2xl text-orange-600 shadow-inner dark:bg-orange-500/15 dark:text-orange-300">
                                🎫
                            </div>
                            <div>
                                <p class="text-sm font-bold text-slate-500 dark:text-slate-400">Открытых заявок</p>
                                <p class="mt-1 text-4xl font-black text-slate-950 dark:text-white">{{ $openTicketsCount }}</p>
                                <p class="mt-1 text-xs font-black text-orange-600 dark:text-orange-300">Нужно проверить</p>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="grid grid-cols-1 gap-6 2xl:grid-cols-[minmax(340px,380px)_minmax(0,1fr)]">
                    <div class="min-w-0">
                        <div class="h-full rounded-[1.7rem] border border-white bg-white p-6 shadow-sm shadow-slate-200/70 dark:border-white/10 dark:bg-white/[0.04] dark:shadow-none">
                            <h2 class="text-xl font-black text-slate-950 dark:text-white">Общая динамика</h2>

                            <div class="mt-5 grid gap-5">
                                <div>
                                    <div class="mb-2 flex items-center justify-between">
                                        <p class="text-sm font-black text-slate-700 dark:text-slate-200">Прогресс студентов</p>
                                        <p class="text-sm font-black text-blue-600 dark:text-blue-300">{{ $overallProgress }}%</p>
                                    </div>

                                    <div class="h-3 overflow-hidden rounded-full bg-slate-100 dark:bg-white/5">
                                        <div class="h-full rounded-full bg-blue-600 dark:bg-blue-400" style="width: {{ min(100, max(0, $overallProgress)) }}%"></div>
                                    </div>
                                </div>

                                <div>
                                    <div class="mb-2 flex items-center justify-between">
                                        <p class="text-sm font-black text-slate-700 dark:text-slate-200">Активные студенты</p>
                                        <p class="text-sm font-black text-emerald-600 dark:text-emerald-300">{{ $activeStudentsCount }} из {{ $assignedStudentsCount }}</p>
                                    </div>

                                    <div class="h-3 overflow-hidden rounded-full bg-slate-100 dark:bg-white/5">
                                        <div class="h-full rounded-full bg-emerald-600 dark:bg-emerald-400" style="width: {{ $assignedStudentsCount > 0 ? min(100, round(($activeStudentsCount / $assignedStudentsCount) * 100)) : 0 }}%"></div>
                                    </div>
                                </div>

                                <div>
                                    <div class="mb-2 flex items-center justify-between">
                                        <p class="text-sm font-black text-slate-700 dark:text-slate-200">Опубликованные тесты</p>
                                        <p class="text-sm font-black text-violet-600 dark:text-violet-300">{{ $publishedTestsCount }} из {{ $testsCount }}</p>
                                    </div>

                                    <div class="h-3 overflow-hidden rounded-full bg-slate-100 dark:bg-white/5">
                                        <div class="h-full rounded-full bg-violet-600 dark:bg-violet-400" style="width: {{ $testsCount > 0 ? min(100, round(($publishedTestsCount / $testsCount) * 100)) : 0 }}%"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6 grid grid-cols-1 gap-3 sm:grid-cols-2">
                                <a href="{{ route('courses.index') }}" class="rounded-2xl border border-slate-200 px-4 py-3 text-center text-sm font-black text-slate-700 transition hover:border-blue-400 hover:text-blue-600 dark:border-white/10 dark:text-slate-200">
                                    Курсы
                                </a>

                                <a href="{{ route('courses.index') }}" class="rounded-2xl border border-slate-200 px-4 py-3 text-center text-sm font-black text-slate-700 transition hover:border-blue-400 hover:text-blue-600 dark:border-white/10 dark:text-slate-200">
                                    Тесты
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="min-w-0">
                        <div class="h-full overflow-hidden rounded-[1.7rem] border border-white bg-white shadow-sm shadow-slate-200/70 dark:border-white/10 dark:bg-white/[0.04] dark:shadow-none">
                            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5 dark:border-white/10">
                                <h2 class="text-xl font-black text-slate-950 dark:text-white">Последние прохождения</h2>
                                <a href="{{ route('courses.index') }}" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-black text-slate-500 transition hover:border-blue-400 hover:text-blue-600 dark:border-white/10 dark:text-slate-300">
                                    Все тесты
                                </a>
                            </div>

                            <div class="p-5">
                                @forelse($latestAttempts as $attempt)
                                    @php
                                        $percent = $attempt->max_score > 0 ? round(($attempt->score / $attempt->max_score) * 100) : 0;
                                        $attemptStatus = $scoreStatus($percent);
                                    @endphp

                                    <div class="relative flex gap-4 border-l-2 border-slate-200 pb-5 pl-6 last:pb-0 dark:border-white/10">
                                        <div class="absolute -left-[9px] top-5 h-4 w-4 rounded-full border-2 border-blue-400 bg-white dark:bg-[#07111f]"></div>

                                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-blue-50 text-lg font-black text-blue-600 dark:bg-blue-500/20 dark:text-blue-300">
                                            {{ mb_substr($attempt->user?->name ?? 'С', 0, 1) }}
                                        </div>

                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-start justify-between gap-4">
                                                <div class="min-w-0">
                                                    <p class="truncate font-black text-slate-950 dark:text-white">
                                                        {{ $attempt->user?->name ?? 'Пользователь удалён' }}
                                                    </p>
                                                    <p class="mt-1 truncate text-sm font-semibold text-slate-500 dark:text-slate-400">
                                                        {{ $attempt->test?->title ?? 'Тест удалён' }} · {{ $attempt->created_at->format('d.m.Y H:i') }}
                                                    </p>
                                                </div>

                                                <div class="shrink-0 text-right">
                                                    <p class="text-xl font-black {{ $attemptStatus['class'] }}">{{ $percent }}%</p>
                                                    <p class="text-xs font-black {{ $attemptStatus['class'] }}">
                                                        {{ $attempt->score }} из {{ $attempt->max_score }}
                                                    </p>
                                                </div>
                                            </div>

                                            <div class="mt-3 h-2 overflow-hidden rounded-full bg-slate-100 dark:bg-white/5">
                                                <div class="h-full rounded-full {{ $attemptStatus['bar'] }}" style="width: {{ min(100, max(0, $percent)) }}%"></div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center dark:border-white/10 dark:bg-white/[0.03]">
                                        <p class="font-black text-slate-950 dark:text-white">Прохождений пока нет</p>
                                        <p class="mt-2 text-sm font-semibold text-slate-500 dark:text-slate-400">
                                            Когда студенты начнут проходить ваши тесты, здесь появятся результаты.
                                        </p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </section>

                <section class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
                    <div class="rounded-[1.7rem] border border-white bg-white p-6 shadow-sm shadow-slate-200/70 dark:border-white/10 dark:bg-white/[0.04] dark:shadow-none">
                        <h2 class="text-lg font-black text-slate-950 dark:text-white">📄 Материалы</h2>
                        <p class="mt-4 text-4xl font-black text-slate-950 dark:text-white">{{ $materialsCount }}</p>
                        <p class="mt-1 text-sm font-semibold text-slate-500 dark:text-slate-400">
                            Учебные материалы в ваших курсах.
                        </p>

                        <a href="{{ route('courses.index') }}" class="mt-5 inline-flex w-full items-center justify-center rounded-2xl border border-slate-200 px-4 py-3 text-sm font-black text-slate-700 transition hover:border-blue-400 hover:text-blue-600 dark:border-white/10 dark:text-slate-200">
                            Открыть курсы
                        </a>
                    </div>

                    <div class="rounded-[1.7rem] border border-white bg-white p-6 shadow-sm shadow-slate-200/70 dark:border-white/10 dark:bg-white/[0.04] dark:shadow-none">
                        <h2 class="text-lg font-black text-slate-950 dark:text-white">🧾 Попытки</h2>
                        <p class="mt-4 text-4xl font-black text-slate-950 dark:text-white">{{ $attemptsCount }}</p>
                        <p class="mt-1 text-sm font-semibold text-slate-500 dark:text-slate-400">
                            Всего прохождений ваших тестов.
                        </p>

                        <a href="{{ route('courses.index') }}" class="mt-5 inline-flex w-full items-center justify-center rounded-2xl border border-slate-200 px-4 py-3 text-sm font-black text-slate-700 transition hover:border-blue-400 hover:text-blue-600 dark:border-white/10 dark:text-slate-200">
                            Смотреть тесты
                        </a>
                    </div>

                    <div class="rounded-[1.7rem] border border-white bg-white p-6 shadow-sm shadow-slate-200/70 dark:border-white/10 dark:bg-white/[0.04] dark:shadow-none">
                        <h2 class="text-lg font-black text-slate-950 dark:text-white">⚡ Быстрые действия</h2>

                        <div class="mt-5 grid gap-3">
                            <a href="{{ route('courses.index') }}" class="rounded-2xl border border-slate-200 px-4 py-3 text-sm font-black text-slate-700 transition hover:border-blue-400 hover:text-blue-600 dark:border-white/10 dark:text-slate-200">
                                Управление курсами
                            </a>

                            <a href="{{ route('schedule.index') }}" class="rounded-2xl border border-slate-200 px-4 py-3 text-sm font-black text-slate-700 transition hover:border-blue-400 hover:text-blue-600 dark:border-white/10 dark:text-slate-200">
                                Расписание
                            </a>

                            <a href="{{ route('support-tickets.index') }}" class="rounded-2xl border border-slate-200 px-4 py-3 text-sm font-black text-slate-700 transition hover:border-blue-400 hover:text-blue-600 dark:border-white/10 dark:text-slate-200">
                                Заявки: {{ $openTicketsCount }}
                            </a>

                            <a href="{{ route('notifications.broadcast.create') }}" class="rounded-2xl border border-slate-200 px-4 py-3 text-sm font-black text-slate-700 transition hover:border-blue-400 hover:text-blue-600 dark:border-white/10 dark:text-slate-200">
                                Отправить уведомление
                            </a>
                        </div>
                    </div>
                </section>

                <section class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-3">
                    <div class="overflow-hidden rounded-[1.7rem] border border-white bg-white shadow-sm shadow-slate-200/70 dark:border-white/10 dark:bg-white/[0.04] dark:shadow-none">
                        <div class="border-b border-slate-100 px-6 py-5 dark:border-white/10">
                            <h2 class="text-xl font-black text-slate-950 dark:text-white">Кто отстаёт</h2>
                        </div>

                        <div class="p-5">
                            @forelse($laggingStudents as $student)
                                <div class="mb-4 last:mb-0 rounded-2xl bg-slate-50 p-4 dark:bg-white/[0.04]">
                                    <div class="flex items-start justify-between gap-4">
                                        <div class="min-w-0">
                                            <p class="truncate font-black text-slate-950 dark:text-white">{{ $student['name'] }}</p>
                                            <p class="mt-1 text-sm font-semibold text-slate-500 dark:text-slate-400">
                                                Пройдено {{ $student['completed_tests_count'] }} из {{ $student['assigned_tests_count'] }}
                                            </p>
                                        </div>

                                        <span class="rounded-full bg-orange-50 px-3 py-1 text-xs font-black text-orange-600 dark:bg-orange-500/15 dark:text-orange-300">
                                            {{ $student['progress_percent'] }}%
                                        </span>
                                    </div>

                                    <div class="mt-3 h-2 overflow-hidden rounded-full bg-slate-100 dark:bg-white/5">
                                        <div class="h-full rounded-full bg-orange-500" style="width: {{ min(100, max(0, $student['progress_percent'])) }}%"></div>
                                    </div>
                                </div>
                            @empty
                                <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center dark:border-white/10 dark:bg-white/[0.03]">
                                    <p class="font-black text-slate-950 dark:text-white">Явных отстающих нет</p>
                                    <p class="mt-2 text-sm font-semibold text-slate-500 dark:text-slate-400">
                                        Это хорошо.
                                    </p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <div class="overflow-hidden rounded-[1.7rem] border border-white bg-white shadow-sm shadow-slate-200/70 dark:border-white/10 dark:bg-white/[0.04] dark:shadow-none">
                        <div class="border-b border-slate-100 px-6 py-5 dark:border-white/10">
                            <h2 class="text-xl font-black text-slate-950 dark:text-white">Активные студенты</h2>
                        </div>

                        <div class="p-5">
                            @forelse($activeStudents as $student)
                                <div class="mb-4 last:mb-0 rounded-2xl bg-slate-50 p-4 dark:bg-white/[0.04]">
                                    <div class="flex items-start justify-between gap-4">
                                        <div class="min-w-0">
                                            <p class="truncate font-black text-slate-950 dark:text-white">{{ $student['name'] }}</p>
                                            <p class="mt-1 text-sm font-semibold text-slate-500 dark:text-slate-400">
                                                Последняя активность: {{ $student['last_attempt_at']?->format('d.m.Y H:i') ?? 'нет данных' }}
                                            </p>
                                        </div>

                                        <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-black text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-300">
                                            {{ $student['attempts_count'] }} попыток
                                        </span>
                                    </div>
                                </div>
                            @empty
                                <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center dark:border-white/10 dark:bg-white/[0.03]">
                                    <p class="font-black text-slate-950 dark:text-white">Активности пока нет</p>
                                    <p class="mt-2 text-sm font-semibold text-slate-500 dark:text-slate-400">
                                        Студенты ещё не начали проходить тесты.
                                    </p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <div class="overflow-hidden rounded-[1.7rem] border border-white bg-white shadow-sm shadow-slate-200/70 dark:border-white/10 dark:bg-white/[0.04] dark:shadow-none">
                        <div class="border-b border-slate-100 px-6 py-5 dark:border-white/10">
                            <h2 class="text-xl font-black text-slate-950 dark:text-white">Не начинали тесты</h2>
                        </div>

                        <div class="p-5">
                            @forelse($studentsWithoutAttempts as $student)
                                <div class="mb-4 last:mb-0 rounded-2xl bg-slate-50 p-4 dark:bg-white/[0.04]">
                                    <div class="flex items-start justify-between gap-4">
                                        <div class="min-w-0">
                                            <p class="truncate font-black text-slate-950 dark:text-white">{{ $student['name'] }}</p>
                                            <p class="mt-1 text-sm font-semibold text-slate-500 dark:text-slate-400">
                                                Курсов: {{ $student['courses_count'] }}
                                            </p>
                                        </div>

                                        <span class="rounded-full bg-rose-50 px-3 py-1 text-xs font-black text-rose-600 dark:bg-rose-500/15 dark:text-rose-300">
                                            0 из {{ $student['assigned_tests_count'] }}
                                        </span>
                                    </div>
                                </div>
                            @empty
                                <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center dark:border-white/10 dark:bg-white/[0.03]">
                                    <p class="font-black text-slate-950 dark:text-white">Все уже стартовали</p>
                                    <p class="mt-2 text-sm font-semibold text-slate-500 dark:text-slate-400">
                                        Никто не прячется от тестов.
                                    </p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </section>

                <section class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-2">
                    <div class="rounded-[1.7rem] border border-white bg-white p-6 shadow-sm shadow-slate-200/70 dark:border-white/10 dark:bg-white/[0.04] dark:shadow-none">
                        <div class="mb-5 flex items-center justify-between">
                            <h2 class="text-xl font-black text-slate-950 dark:text-white">Мои курсы</h2>
                            <a href="{{ route('courses.index') }}" class="text-sm font-black text-blue-600 dark:text-blue-300">
                                Все курсы →
                            </a>
                        </div>

                        <div class="grid gap-4">
                            @forelse($teacherCourses as $course)
                                <div class="rounded-2xl bg-slate-50 p-4 dark:bg-white/[0.04]">
                                    <div class="flex items-start justify-between gap-4">
                                        <div class="min-w-0">
                                            <p class="truncate font-black text-slate-950 dark:text-white">{{ $course->title }}</p>
                                            <p class="mt-1 text-sm font-semibold text-slate-500 dark:text-slate-400">
                                                Студентов: {{ $course->students_count }} · Материалов: {{ $course->materials_count }} · Тестов: {{ $course->tests_count }}
                                            </p>
                                        </div>

                                        <span class="shrink-0 rounded-full bg-blue-50 px-3 py-1 text-xs font-black text-blue-600 dark:bg-blue-500/15 dark:text-blue-300">
                                            {{ $course->published_tests_count }} опубликовано
                                        </span>
                                    </div>
                                </div>
                            @empty
                                <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center dark:border-white/10 dark:bg-white/[0.03]">
                                    <p class="font-black text-slate-950 dark:text-white">Курсов пока нет</p>
                                    <p class="mt-2 text-sm font-semibold text-slate-500 dark:text-slate-400">
                                        Создайте первый курс, и он появится здесь.
                                    </p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <div class="rounded-[1.7rem] border border-white bg-white p-6 shadow-sm shadow-slate-200/70 dark:border-white/10 dark:bg-white/[0.04] dark:shadow-none">
                        <div class="mb-5 flex items-center justify-between">
                            <h2 class="text-xl font-black text-slate-950 dark:text-white">Ближайшее расписание</h2>
                            <a href="{{ route('schedule.index') }}" class="text-sm font-black text-blue-600 dark:text-blue-300">
                                Всё расписание →
                            </a>
                        </div>

                        <div class="grid gap-4">
                            @forelse($upcomingEvents as $event)
                                <div class="rounded-2xl bg-slate-50 p-4 dark:bg-white/[0.04]">
                                    <p class="text-sm font-black text-blue-600 dark:text-blue-300">
                                        {{ $event->starts_at->format('d.m.Y H:i') }}
                                    </p>

                                    <p class="mt-2 font-black text-slate-950 dark:text-white">
                                        {{ $event->title ?? $event->course?->title ?? 'Занятие' }}
                                    </p>

                                    <p class="mt-1 text-sm font-semibold text-slate-500 dark:text-slate-400">
                                        {{ $eventTypeLabels[$event->type] ?? 'Событие' }}
                                        @if($event->classGroup)
                                            · {{ $event->classGroup->name }}
                                        @endif
                                        @if($event->location)
                                            · {{ $event->location }}
                                        @endif
                                    </p>
                                </div>
                            @empty
                                <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center dark:border-white/10 dark:bg-white/[0.03]">
                                    <p class="font-black text-slate-950 dark:text-white">Ближайших занятий пока нет</p>
                                    <p class="mt-2 text-sm font-semibold text-slate-500 dark:text-slate-400">
                                        Расписание пустое. Но это легко исправить.
                                    </p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </section>

                <section class="mt-6 rounded-[1.7rem] border border-white bg-white p-6 shadow-sm shadow-slate-200/70 dark:border-white/10 dark:bg-white/[0.04] dark:shadow-none">
                    <div class="mb-5 flex items-center justify-between">
                        <h2 class="text-xl font-black text-slate-950 dark:text-white">Последние заявки</h2>
                        <a href="{{ route('support-tickets.index') }}" class="text-sm font-black text-blue-600 dark:text-blue-300">
                            Все заявки →
                        </a>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        @forelse($latestTickets as $ticket)
                            <div class="rounded-2xl bg-slate-50 p-4 dark:bg-white/[0.04]">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="min-w-0">
                                        <p class="truncate font-black text-slate-950 dark:text-white">{{ $ticket->subject }}</p>
                                        <p class="mt-1 text-sm font-semibold text-slate-500 dark:text-slate-400">
                                            {{ $ticket->user?->name ?? 'Пользователь удалён' }}
                                            @if($ticket->course)
                                                · {{ $ticket->course->title }}
                                            @endif
                                        </p>
                                    </div>

                                    <span class="shrink-0 rounded-full bg-orange-50 px-3 py-1 text-xs font-black text-orange-600 dark:bg-orange-500/15 dark:text-orange-300">
                                        {{ $ticketStatusLabels[$ticket->status] ?? $ticket->status }}
                                    </span>
                                </div>
                            </div>
                        @empty
                            <div class="md:col-span-2 rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center dark:border-white/10 dark:bg-white/[0.03]">
                                <p class="font-black text-slate-950 dark:text-white">Заявок пока нет</p>
                                <p class="mt-2 text-sm font-semibold text-slate-500 dark:text-slate-400">
                                    Можно выдохнуть. Но ненадолго.
                                </p>
                            </div>
                        @endforelse
                    </div>
                </section>
            </div>
        </div>
    </div>
</x-app-layout>
