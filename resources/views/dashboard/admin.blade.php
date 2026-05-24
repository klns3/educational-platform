<x-app-layout>
    @php
        $averageScoreValue = round((float) $averageScore, 2);

        $scoreStatus = function (int|float $score) {
            if ($score >= 85) {
                return ['text' => 'Система сильная', 'class' => 'text-emerald-500', 'bar' => 'bg-emerald-500'];
            }

            if ($score >= 70) {
                return ['text' => 'Хорошая динамика', 'class' => 'text-amber-500', 'bar' => 'bg-amber-500'];
            }

            return ['text' => 'Нужно внимание', 'class' => 'text-orange-500', 'bar' => 'bg-orange-500'];
        };

        $score = $scoreStatus($averageScoreValue);

        $roleStats = [
            [
                'label' => 'Студенты',
                'count' => $studentsCount,
                'percent' => $studentsPercent,
                'class' => 'bg-blue-600 dark:bg-blue-400',
                'text' => 'text-blue-600 dark:text-blue-300',
            ],
            [
                'label' => 'Преподаватели',
                'count' => $teachersCount,
                'percent' => $teachersPercent,
                'class' => 'bg-violet-600 dark:bg-violet-400',
                'text' => 'text-violet-600 dark:text-violet-300',
            ],
            [
                'label' => 'Администраторы',
                'count' => $adminsCount,
                'percent' => $adminsPercent,
                'class' => 'bg-emerald-600 dark:bg-emerald-400',
                'text' => 'text-emerald-600 dark:text-emerald-300',
            ],
            [
                'label' => 'Без роли',
                'count' => $pendingUsersCount,
                'percent' => $pendingPercent,
                'class' => 'bg-orange-500 dark:bg-orange-400',
                'text' => 'text-orange-600 dark:text-orange-300',
            ],
        ];

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
                            Панель администратора
                        </p>

                        <h1 class="text-3xl font-black tracking-tight text-slate-950 sm:text-4xl dark:text-white">
                            Управление образовательной платформой
                        </h1>

                        <p class="mt-3 max-w-2xl text-sm font-semibold text-slate-500 sm:text-base dark:text-slate-300">
                            Пользователи, курсы, тесты, заявки, расписание и журнал действий собраны в одном месте.
                    </div>
                </section>

                @if($pendingUsersCount > 0)
                    <section class="glass-panel mb-6 overflow-hidden rounded-[1.7rem] border border-orange-200/70 bg-orange-50/75 shadow-sm shadow-orange-100/70 dark:border-orange-400/20 dark:bg-orange-500/10 dark:shadow-none">
                        <div class="grid gap-5 p-6 lg:grid-cols-[1fr_220px] lg:items-center">
                            <div>
                                <p class="text-sm font-black text-orange-600 dark:text-orange-300">Требуется действие</p>

                                <h2 class="mt-2 text-2xl font-black text-orange-950 dark:text-white">
                                    Новые пользователи ожидают назначения роли
                                </h2>

                                <p class="mt-2 text-sm font-semibold text-orange-800 dark:text-orange-200">
                                    Сейчас {{ $pendingUsersCount }} пользователь(ей) не имеют роли и не могут пользоваться системой.
                                </p>

                                <div class="mt-4 grid gap-2 sm:grid-cols-2">
                                    @foreach($pendingUsers as $pendingUser)
                                        <div class="glass-chip rounded-2xl px-4 py-3">
                                            <p class="truncate text-sm font-black text-slate-950 dark:text-white">{{ $pendingUser->name }}</p>
                                            <p class="truncate text-xs font-semibold text-slate-500 dark:text-slate-400">{{ $pendingUser->email }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <a href="{{ route('users.index', ['role' => 'pending']) }}"
                               class="inline-flex items-center justify-center rounded-2xl bg-orange-500 px-5 py-4 text-sm font-black text-white shadow-lg shadow-orange-500/20 transition hover:-translate-y-0.5 hover:bg-orange-600">
                                Назначить роли
                            </a>
                        </div>
                    </section>
                @endif

                <section class="mb-6 grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
                    <div class="glass-panel group rounded-[1.7rem] p-5 transition hover:-translate-y-1 hover:shadow-xl hover:shadow-blue-100 dark:shadow-none">
                        <div class="flex items-center gap-4">
                            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-50 text-2xl text-blue-600 shadow-inner dark:bg-blue-500/15 dark:text-blue-300">
                                👥
                            </div>
                            <div>
                                <p class="text-sm font-bold text-slate-500 dark:text-slate-400">Пользователей</p>
                                <p class="mt-1 text-4xl font-black text-slate-950 dark:text-white">{{ $usersCount }}</p>
                                <p class="mt-1 text-xs font-black text-blue-600 dark:text-blue-300">Все аккаунты</p>
                            </div>
                        </div>
                    </div>

                    <div class="glass-panel group rounded-[1.7rem] p-5 transition hover:-translate-y-1 hover:shadow-xl hover:shadow-emerald-100 dark:shadow-none">
                        <div class="flex items-center gap-4">
                            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-50 text-2xl text-emerald-600 shadow-inner dark:bg-emerald-500/15 dark:text-emerald-300">
                                📚
                            </div>
                            <div>
                                <p class="text-sm font-bold text-slate-500 dark:text-slate-400">Курсов</p>
                                <p class="mt-1 text-4xl font-black text-slate-950 dark:text-white">{{ $coursesCount }}</p>
                                <p class="mt-1 text-xs font-black text-emerald-600 dark:text-emerald-300">Учебные направления</p>
                            </div>
                        </div>
                    </div>

                    <div class="glass-panel group rounded-[1.7rem] p-5 transition hover:-translate-y-1 hover:shadow-xl hover:shadow-violet-100 dark:shadow-none">
                        <div class="flex items-center gap-4">
                            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-violet-50 text-2xl text-violet-600 shadow-inner dark:bg-violet-500/15 dark:text-violet-300">
                                🧪
                            </div>
                            <div>
                                <p class="text-sm font-bold text-slate-500 dark:text-slate-400">Тестов</p>
                                <p class="mt-1 text-4xl font-black text-slate-950 dark:text-white">{{ $testsCount }}</p>
                                <p class="mt-1 text-xs font-black text-violet-600 dark:text-violet-300">Контроль знаний</p>
                            </div>
                        </div>
                    </div>

                    <div class="glass-panel group rounded-[1.7rem] p-5 transition hover:-translate-y-1 hover:shadow-xl hover:shadow-orange-100 dark:shadow-none">
                        <div class="flex items-center gap-4">
                            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-orange-50 text-2xl text-orange-600 shadow-inner dark:bg-orange-500/15 dark:text-orange-300">
                                🎫
                            </div>
                            <div>
                                <p class="text-sm font-bold text-slate-500 dark:text-slate-400">Открытых заявок</p>
                                <p class="mt-1 text-4xl font-black text-slate-950 dark:text-white">{{ $openTicketsCount }}</p>
                                <p class="mt-1 text-xs font-black text-orange-600 dark:text-orange-300">Нужно обработать</p>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="grid grid-cols-1 gap-6 2xl:grid-cols-[minmax(340px,380px)_minmax(0,1fr)]">
                    <div class="min-w-0">
                        <div class="glass-panel h-full rounded-[1.7rem] p-6 dark:shadow-none">
                            <h2 class="text-xl font-black text-slate-950 dark:text-white">Структура пользователей</h2>

                            <div class="mt-5 grid gap-4">
                                @foreach($roleStats as $role)
                                    <div>
                                        <div class="mb-2 flex items-center justify-between gap-4">
                                            <p class="text-sm font-black text-slate-700 dark:text-slate-200">
                                                {{ $role['label'] }}
                                            </p>
                                            <p class="text-sm font-black {{ $role['text'] }}">
                                                {{ $role['count'] }} · {{ $role['percent'] }}%
                                            </p>
                                        </div>

                                        <div class="h-3 overflow-hidden rounded-full bg-slate-100 dark:bg-white/5">
                                            <div class="h-full rounded-full {{ $role['class'] }}" style="width: {{ min(100, max(0, $role['percent'])) }}%"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mt-6 grid grid-cols-1 gap-3 sm:grid-cols-2">
                                <a href="{{ route('users.index') }}" class="rounded-2xl border border-slate-200 px-4 py-3 text-center text-sm font-black text-slate-700 transition hover:border-blue-400 hover:text-blue-600 dark:border-white/10 dark:text-slate-200">
                                    Пользователи
                                </a>

                                <a href="{{ route('class-groups.index') }}" class="rounded-2xl border border-slate-200 px-4 py-3 text-center text-sm font-black text-slate-700 transition hover:border-blue-400 hover:text-blue-600 dark:border-white/10 dark:text-slate-200">
                                    Группы: {{ $classGroupsCount }}
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="min-w-0">
                        <div class="glass-panel h-full overflow-hidden rounded-[1.7rem] dark:shadow-none">
                            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5 dark:border-white/10">
                                <h2 class="text-xl font-black text-slate-950 dark:text-white">Последние попытки тестов</h2>
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
                                        <p class="font-black text-slate-950 dark:text-white">Попыток пока нет</p>
                                        <p class="mt-2 text-sm font-semibold text-slate-500 dark:text-slate-400">
                                            Когда ученики начнут проходить тесты, здесь появится активность.
                                        </p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </section>

                <section class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
                    <div class="glass-panel rounded-[1.7rem] p-6 dark:shadow-none">
                        <h2 class="text-lg font-black text-slate-950 dark:text-white">📄 Контент</h2>

                        <div class="mt-5 grid gap-3">
                            <div class="glass-chip flex items-center justify-between rounded-2xl px-4 py-3">
                                <span class="text-sm font-bold text-slate-500 dark:text-slate-400">Материалы</span>
                                <span class="font-black text-slate-950 dark:text-white">{{ $materialsCount }}</span>
                            </div>

                            <div class="glass-chip flex items-center justify-between rounded-2xl px-4 py-3">
                                <span class="text-sm font-bold text-slate-500 dark:text-slate-400">Попытки</span>
                                <span class="font-black text-slate-950 dark:text-white">{{ $attemptsCount }}</span>
                            </div>

                            <div class="glass-chip flex items-center justify-between rounded-2xl px-4 py-3">
                                <span class="text-sm font-bold text-slate-500 dark:text-slate-400">Активные коды</span>
                                <span class="font-black text-slate-950 dark:text-white">{{ $activeInvitationCodesCount }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="glass-panel rounded-[1.7rem] p-6 dark:shadow-none">
                        <h2 class="text-lg font-black text-slate-950 dark:text-white">🗓 Расписание</h2>

                        <p class="mt-4 text-4xl font-black text-slate-950 dark:text-white">{{ $todayEventsCount }}</p>
                        <p class="mt-1 text-sm font-semibold text-slate-500 dark:text-slate-400">
                            Событий запланировано на сегодня.
                        </p>

                        <div class="mt-5 h-3 overflow-hidden rounded-full bg-slate-100 dark:bg-white/5">
                            <div class="h-full rounded-full bg-blue-600 dark:bg-blue-400" style="width: {{ $scheduleEventsCount > 0 ? min(100, max(8, round(($todayEventsCount / $scheduleEventsCount) * 100))) : 0 }}%"></div>
                        </div>

                        <a href="{{ route('schedule.index') }}" class="mt-5 inline-flex w-full items-center justify-center rounded-2xl border border-slate-200 px-4 py-3 text-sm font-black text-slate-700 transition hover:border-blue-400 hover:text-blue-600 dark:border-white/10 dark:text-slate-200">
                            Открыть расписание
                        </a>
                    </div>

                    <div class="glass-panel rounded-[1.7rem] p-6 dark:shadow-none">
                        <h2 class="text-lg font-black text-slate-950 dark:text-white">⚡ Быстрые действия</h2>

                        <div class="mt-5 grid gap-3">
                            <a href="{{ route('users.index') }}" class="rounded-2xl border border-slate-200 px-4 py-3 text-sm font-black text-slate-700 transition hover:border-blue-400 hover:text-blue-600 dark:border-white/10 dark:text-slate-200">
                                Управление пользователями
                            </a>

                            <a href="{{ route('courses.index') }}" class="rounded-2xl border border-slate-200 px-4 py-3 text-sm font-black text-slate-700 transition hover:border-blue-400 hover:text-blue-600 dark:border-white/10 dark:text-slate-200">
                                Управление курсами
                            </a>

                            <a href="{{ route('invitation-codes.index') }}" class="rounded-2xl border border-slate-200 px-4 py-3 text-sm font-black text-slate-700 transition hover:border-blue-400 hover:text-blue-600 dark:border-white/10 dark:text-slate-200">
                                Пригласительные коды
                            </a>

                            <a href="{{ route('action-logs.index') }}" class="rounded-2xl border border-slate-200 px-4 py-3 text-sm font-black text-slate-700 transition hover:border-blue-400 hover:text-blue-600 dark:border-white/10 dark:text-slate-200">
                                Журнал действий
                            </a>
                        </div>
                    </div>
                </section>

                <section class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-2">
                    <div class="glass-panel overflow-hidden rounded-[1.7rem] dark:shadow-none">
                        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5 dark:border-white/10">
                            <h2 class="text-xl font-black text-slate-950 dark:text-white">Последние заявки</h2>
                            <a href="{{ route('support-tickets.index') }}" class="text-sm font-black text-blue-600 dark:text-blue-300">
                                Все заявки →
                            </a>
                        </div>

                        <div class="divide-y divide-slate-100 p-5 dark:divide-white/10">
                            @forelse($latestTickets as $ticket)
                                <div class="py-4 first:pt-0 last:pb-0">
                                    <div class="flex items-start justify-between gap-4">
                                        <div class="min-w-0">
                                            <p class="truncate font-black text-slate-950 dark:text-white">
                                                {{ $ticket->subject }}
                                            </p>
                                            <p class="mt-1 text-sm font-semibold text-slate-500 dark:text-slate-400">
                                                {{ $ticket->user?->name ?? 'Пользователь удалён' }} · {{ $ticket->created_at->format('d.m.Y H:i') }}
                                            </p>
                                        </div>

                                        <span class="shrink-0 rounded-full bg-orange-50 px-3 py-1 text-xs font-black text-orange-600 dark:bg-orange-500/15 dark:text-orange-300">
                                            {{ $ticketStatusLabels[$ticket->status] ?? $ticket->status }}
                                        </span>
                                    </div>
                                </div>
                            @empty
                                <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center dark:border-white/10 dark:bg-white/[0.03]">
                                    <p class="font-black text-slate-950 dark:text-white">Заявок пока нет</p>
                                    <p class="mt-2 text-sm font-semibold text-slate-500 dark:text-slate-400">
                                        Тихо. Даже слишком тихо.
                                    </p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <div class="glass-panel overflow-hidden rounded-[1.7rem] dark:shadow-none">
                        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5 dark:border-white/10">
                            <h2 class="text-xl font-black text-slate-950 dark:text-white">Последние действия</h2>
                            <a href="{{ route('action-logs.index') }}" class="text-sm font-black text-blue-600 dark:text-blue-300">
                                Весь журнал →
                            </a>
                        </div>

                        <div class="p-5">
                            @forelse($latestLogs as $log)
                                <div class="relative flex gap-4 border-l-2 border-slate-200 pb-5 pl-6 last:pb-0 dark:border-white/10">
                                    <div class="absolute -left-[9px] top-5 h-4 w-4 rounded-full border-2 border-violet-400 bg-white dark:bg-[#07111f]"></div>

                                    <div class="min-w-0">
                                        <p class="font-black text-slate-950 dark:text-white">{{ $log->action }}</p>
                                        <p class="mt-1 text-sm font-semibold text-slate-500 dark:text-slate-400">
                                            {{ $log->description }}
                                        </p>
                                        <p class="mt-2 text-xs font-bold text-slate-400 dark:text-slate-500">
                                            {{ $log->user?->name ?? 'Система' }} · {{ $log->created_at->format('d.m.Y H:i') }}
                                        </p>
                                    </div>
                                </div>
                            @empty
                                <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center dark:border-white/10 dark:bg-white/[0.03]">
                                    <p class="font-black text-slate-950 dark:text-white">Действий пока нет</p>
                                    <p class="mt-2 text-sm font-semibold text-slate-500 dark:text-slate-400">
                                        Журнал пока чистый.
                                    </p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </section>

                <section class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-2">
                    <div class="glass-panel rounded-[1.7rem] p-6 dark:shadow-none">
                        <div class="mb-5 flex items-center justify-between">
                            <h2 class="text-xl font-black text-slate-950 dark:text-white">Новые пользователи</h2>
                            <a href="{{ route('users.index') }}" class="text-sm font-black text-blue-600 dark:text-blue-300">
                                Все пользователи →
                            </a>
                        </div>

                        <div class="grid gap-3">
                            @forelse($latestUsers as $user)
                                <div class="glass-chip flex items-center justify-between gap-4 rounded-2xl p-4">
                                    <div class="min-w-0">
                                        <p class="truncate font-black text-slate-950 dark:text-white">{{ $user->name }}</p>
                                        <p class="truncate text-sm font-semibold text-slate-500 dark:text-slate-400">{{ $user->email }}</p>
                                    </div>

                                    <span class="shrink-0 rounded-full bg-blue-50 px-3 py-1 text-xs font-black text-blue-600 dark:bg-blue-500/15 dark:text-blue-300">
                                        {{ $user->role ?? 'без роли' }}
                                    </span>
                                </div>
                            @empty
                                <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center dark:border-white/10 dark:bg-white/[0.03]">
                                    <p class="font-black text-slate-950 dark:text-white">Пользователей пока нет</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <div class="glass-panel rounded-[1.7rem] p-6 dark:shadow-none">
                        <div class="mb-5 flex items-center justify-between">
                            <h2 class="text-xl font-black text-slate-950 dark:text-white">Ближайшее расписание</h2>
                            <a href="{{ route('schedule.index') }}" class="text-sm font-black text-blue-600 dark:text-blue-300">
                                Всё расписание →
                            </a>
                        </div>

                        <div class="grid gap-4">
                            @forelse($upcomingEvents as $event)
                                <div class="glass-chip rounded-2xl p-4">
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
                                    <p class="font-black text-slate-950 dark:text-white">Ближайших событий пока нет</p>
                                    <p class="mt-2 text-sm font-semibold text-slate-500 dark:text-slate-400">
                                        Расписание чистое. Редкий случай, когда пустота радует.
                                    </p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</x-app-layout>
