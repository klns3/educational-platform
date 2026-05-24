@php
    use App\Models\ScheduleEvent;

    $labels = [
        'heading' => 'Расписание',
        'student_subtitle' => 'События вашей группы на неделю',
        'teacher_subtitle' => 'Ваши события по группам',
        'admin_subtitle' => 'Все события расписания',
        'add' => 'Добавить событие',
        'today' => 'Сегодня',
        'week' => 'Неделя',
        'previous_week' => 'Предыдущая',
        'next_week' => 'Следующая',
        'group' => 'Группа',
        'teacher' => 'Преподаватель',
        'course' => 'Курс',
        'location' => 'Место',
        'description' => 'Описание',
        'edit' => 'Изменить',
        'delete' => 'Удалить',
        'delete_confirm' => 'Удалить событие расписания?',
        'no_events_today' => 'На сегодня событий нет',
        'no_events_day' => 'В этот день событий нет',
        'no_group' => 'Вы пока не прикреплены к учебной группе',
        'no_groups_for_teacher' => 'У вас пока нет доступных групп для расписания',
        'lesson' => 'Занятие',
        'consultation' => 'Консультация',
        'exam' => 'Экзамен',
        'other' => 'Другое',
    ];

    $weekdayLabels = [
        1 => 'Пн',
        2 => 'Вт',
        3 => 'Ср',
        4 => 'Чт',
        5 => 'Пт',
        6 => 'Сб',
        7 => 'Вс',
    ];

    $typeClasses = [
        ScheduleEvent::TYPE_LESSON => 'bg-blue-50 text-blue-600 dark:bg-blue-500/15 dark:text-blue-300',
        ScheduleEvent::TYPE_CONSULTATION => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-300',
        ScheduleEvent::TYPE_EXAM => 'bg-rose-50 text-rose-600 dark:bg-rose-500/15 dark:text-rose-300',
        ScheduleEvent::TYPE_OTHER => 'bg-slate-100 text-slate-600 dark:bg-white/10 dark:text-slate-300',
    ];

    $subtitle = match (auth()->user()->role) {
        'admin' => $labels['admin_subtitle'],
        'teacher' => $labels['teacher_subtitle'],
        default => $labels['student_subtitle'],
    };
@endphp

<x-app-layout>
    <div class="min-h-screen bg-[#f6f8fc] text-slate-950 transition-colors duration-300 dark:bg-[#07111f] dark:text-white">
        <div class="relative overflow-hidden">

            <div class="relative mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
                <section class="mb-7 flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="mb-4 inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700 dark:border-blue-400/20 dark:bg-blue-500/10 dark:text-blue-200">
                            Учебный календарь
                        </p>

                        <h1 class="text-3xl font-black tracking-tight text-slate-950 sm:text-4xl dark:text-white">
                            {{ $labels['heading'] }}
                        </h1>

                        <p class="mt-3 max-w-2xl text-sm font-semibold text-slate-500 sm:text-base dark:text-slate-300">
                            {{ $subtitle }}
                        </p>
                    </div>

                    @if($canManageSchedule && $hasAvailableGroups)
                        <a href="{{ route('schedule.create') }}"
                           class="inline-flex items-center justify-center rounded-2xl bg-blue-600 px-6 py-3 text-sm font-black text-white shadow-lg shadow-blue-600/20 transition hover:-translate-y-0.5 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-400">
                            {{ $labels['add'] }}
                        </a>
                    @endif
                </section>

                @if(session('success'))
                    <div class="mb-6 rounded-[1.4rem] border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-black text-emerald-700 dark:border-emerald-400/20 dark:bg-emerald-500/10 dark:text-emerald-200">
                        {{ session('success') }}
                    </div>
                @endif

                @if(auth()->user()->role === 'student' && !$studentHasGroup)
                    <section class="rounded-[1.7rem] border border-white bg-white p-10 text-center shadow-sm shadow-slate-200/70 dark:border-white/10 dark:bg-white/[0.04] dark:shadow-none">
                        <div class="mx-auto mb-5 flex h-20 w-20 items-center justify-center rounded-[1.7rem] bg-blue-50 text-4xl dark:bg-blue-500/15">
                            🗓
                        </div>

                        <p class="text-lg font-black text-slate-950 dark:text-white">
                            {{ $labels['no_group'] }}
                        </p>
                    </section>
                @else
                    @if($canManageSchedule && !$hasAvailableGroups)
                        <div class="mb-6 rounded-[1.4rem] border border-amber-200 bg-amber-50 px-5 py-4 text-sm font-black text-amber-700 dark:border-amber-400/20 dark:bg-amber-500/10 dark:text-amber-200">
                            {{ $labels['no_groups_for_teacher'] }}
                        </div>
                    @endif

                    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                        <section class="overflow-hidden rounded-[1.7rem] border border-white bg-white shadow-sm shadow-slate-200/70 lg:col-span-1 dark:border-white/10 dark:bg-white/[0.04] dark:shadow-none">
                            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5 dark:border-white/10">
                                <h2 class="text-xl font-black text-slate-950 dark:text-white">
                                    {{ $labels['today'] }}
                                </h2>

                                <span class="text-sm font-bold text-slate-400 dark:text-slate-500">
                                    {{ now()->format('d.m.Y') }}
                                </span>
                            </div>

                            <div class="grid gap-4 p-5">
                                @forelse($todayEvents as $event)
                                    <article class="rounded-[1.4rem] border border-slate-200 bg-slate-50 p-4 dark:border-white/10 dark:bg-white/[0.03]">
                                        <div class="mb-3 flex items-center justify-between gap-3">
                                            <span class="rounded-full px-3 py-1 text-xs font-black {{ $typeClasses[$event->type] }}">
                                                {{ $labels[$event->type] }}
                                            </span>

                                            <span class="text-sm font-black text-slate-700 dark:text-slate-200">
                                                {{ $event->starts_at->format('H:i') }} – {{ $event->ends_at->format('H:i') }}
                                            </span>
                                        </div>

                                        <p class="font-black text-slate-950 dark:text-white">
                                            {{ $event->title }}
                                        </p>

                                        <p class="mt-2 text-sm font-semibold text-slate-500 dark:text-slate-400">
                                            {{ $labels['group'] }}:
                                            <span class="font-black text-slate-800 dark:text-slate-200">{{ $event->classGroup?->name }}</span>
                                        </p>

                                        @if(auth()->user()->role === 'student')
                                            <p class="mt-1 text-sm font-semibold text-slate-500 dark:text-slate-400">
                                                {{ $labels['teacher'] }}:
                                                <span class="font-black text-slate-800 dark:text-slate-200">{{ $event->teacher?->name }}</span>
                                            </p>
                                        @endif
                                    </article>
                                @empty
                                    <div class="rounded-[1.4rem] border border-dashed border-slate-300 bg-slate-50 p-6 text-center dark:border-white/10 dark:bg-white/[0.03]">
                                        <p class="text-sm font-black text-slate-500 dark:text-slate-400">
                                            {{ $labels['no_events_today'] }}
                                        </p>
                                    </div>
                                @endforelse
                            </div>
                        </section>

                        <section class="overflow-hidden rounded-[1.7rem] border border-white bg-white shadow-sm shadow-slate-200/70 lg:col-span-2 dark:border-white/10 dark:bg-white/[0.04] dark:shadow-none">
                            <div class="flex flex-col gap-4 border-b border-slate-100 px-6 py-5 sm:flex-row sm:items-center sm:justify-between dark:border-white/10">
                                <div>
                                    <h2 class="text-xl font-black text-slate-950 dark:text-white">
                                        {{ $labels['week'] }}
                                    </h2>

                                    <p class="mt-1 text-sm font-semibold text-slate-500 dark:text-slate-400">
                                        {{ $weekStart->format('d.m.Y') }} – {{ $weekEnd->format('d.m.Y') }}
                                    </p>
                                </div>

                                <div class="flex flex-wrap gap-2">
                                    <a href="{{ route('schedule.index', ['week_start' => $previousWeekStart]) }}"
                                       class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-4 py-2 text-sm font-black text-slate-700 transition hover:border-blue-400 hover:text-blue-600 dark:border-white/10 dark:text-slate-200">
                                        {{ $labels['previous_week'] }}
                                    </a>

                                    <a href="{{ route('schedule.index', ['week_start' => $nextWeekStart]) }}"
                                       class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-4 py-2 text-sm font-black text-slate-700 transition hover:border-blue-400 hover:text-blue-600 dark:border-white/10 dark:text-slate-200">
                                        {{ $labels['next_week'] }}
                                    </a>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-4 p-5 xl:grid-cols-2">
                                @foreach($weekDays as $day)
                                    @php
                                        $dayEvents = $eventsByDay->get($day->toDateString(), collect());
                                    @endphp

                                    <article class="rounded-[1.4rem] border border-slate-200 bg-slate-50 p-4 dark:border-white/10 dark:bg-white/[0.03]">
                                        <div class="mb-4 flex items-center justify-between">
                                            <div>
                                                <p class="text-sm font-black text-blue-600 dark:text-blue-300">
                                                    {{ $weekdayLabels[$day->dayOfWeekIso] }}
                                                </p>

                                                <h3 class="text-lg font-black text-slate-950 dark:text-white">
                                                    {{ $day->format('d.m') }}
                                                </h3>
                                            </div>
                                        </div>

                                        <div class="grid gap-3">
                                            @forelse($dayEvents as $event)
                                                <div class="rounded-2xl border border-slate-200 bg-white p-4 dark:border-white/10 dark:bg-white/[0.04]">
                                                    <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                                                        <span class="rounded-full px-3 py-1 text-xs font-black {{ $typeClasses[$event->type] }}">
                                                            {{ $labels[$event->type] }}
                                                        </span>

                                                        <span class="text-sm font-black text-slate-700 dark:text-slate-200">
                                                            {{ $event->starts_at->format('H:i') }} – {{ $event->ends_at->format('H:i') }}
                                                        </span>
                                                    </div>

                                                    <p class="text-lg font-black text-slate-950 dark:text-white">
                                                        {{ $event->title }}
                                                    </p>

                                                    <div class="mt-3 grid gap-1 text-sm font-semibold text-slate-500 dark:text-slate-400">
                                                        <p>{{ $labels['group'] }}: <span class="font-black text-slate-800 dark:text-slate-200">{{ $event->classGroup?->name }}</span></p>

                                                        @if(auth()->user()->role !== 'teacher')
                                                            <p>{{ $labels['teacher'] }}: <span class="font-black text-slate-800 dark:text-slate-200">{{ $event->teacher?->name }}</span></p>
                                                        @endif

                                                        @if($event->course)
                                                            <p>{{ $labels['course'] }}: <span class="font-black text-slate-800 dark:text-slate-200">{{ $event->course->title }}</span></p>
                                                        @endif

                                                        @if($event->location)
                                                            <p>{{ $labels['location'] }}: <span class="font-black text-slate-800 dark:text-slate-200">{{ $event->location }}</span></p>
                                                        @endif

                                                        @if($event->description)
                                                            <p>{{ $labels['description'] }}: <span class="font-black text-slate-800 dark:text-slate-200">{{ $event->description }}</span></p>
                                                        @endif
                                                    </div>

                                                    @if($canManageSchedule)
                                                        <div class="mt-4 flex flex-wrap gap-2">
                                                            <a href="{{ route('schedule.edit', $event) }}"
                                                               class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-4 py-2 text-sm font-black text-slate-700 transition hover:border-blue-400 hover:text-blue-600 dark:border-white/10 dark:text-slate-200">
                                                                {{ $labels['edit'] }}
                                                            </a>

                                                            <form method="POST"
                                                                  action="{{ route('schedule.destroy', $event) }}"
                                                                  onsubmit="return confirm('{{ $labels['delete_confirm'] }}')">
                                                                @csrf
                                                                @method('DELETE')

                                                                <button class="inline-flex items-center justify-center rounded-xl border border-red-200 bg-red-50 px-4 py-2 text-sm font-black text-red-600 transition hover:border-red-400 hover:bg-red-100 dark:border-red-400/20 dark:bg-red-500/10 dark:text-red-300 dark:hover:bg-red-500/15">
                                                                    {{ $labels['delete'] }}
                                                                </button>
                                                            </form>
                                                        </div>
                                                    @endif
                                                </div>
                                            @empty
                                                <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-4 text-sm font-black text-slate-400 dark:border-white/10 dark:bg-white/[0.04]">
                                                    {{ $labels['no_events_day'] }}
                                                </div>
                                            @endforelse
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        </section>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>