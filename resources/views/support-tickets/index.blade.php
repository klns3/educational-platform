@php
    use App\Models\SupportTicket;

    $labels = [
        'heading' => 'Заявки и обращения',
        'student_subtitle' => 'Ваши обращения по курсам и техническим вопросам',
        'teacher_subtitle' => 'Ваши и адресованные вам заявки',
        'admin_subtitle' => 'Все заявки системы',
        'create' => 'Создать заявку',
        'author' => 'Автор',
        'course' => 'Курс',
        'teacher' => 'Преподаватель',
        'date' => 'Создано',
        'open' => 'Открыть',
        'empty' => 'Заявок пока нет',
        'technical_issue' => 'Техническая проблема',
        'course_question' => 'Вопрос по курсу',
        'teacher_request' => 'Заявка преподавателю',
        'new' => 'Новая',
        'in_progress' => 'В работе',
        'closed' => 'Закрыта',
        'not_assigned' => 'Не назначен',
        'no_course' => 'Без курса',
    ];

    $typeClasses = [
        SupportTicket::TYPE_TECHNICAL_ISSUE => 'bg-rose-50 text-rose-600 dark:bg-rose-500/15 dark:text-rose-300',
        SupportTicket::TYPE_COURSE_QUESTION => 'bg-blue-50 text-blue-600 dark:bg-blue-500/15 dark:text-blue-300',
        SupportTicket::TYPE_TEACHER_REQUEST => 'bg-violet-50 text-violet-600 dark:bg-violet-500/15 dark:text-violet-300',
    ];

    $statusClasses = [
        SupportTicket::STATUS_NEW => 'bg-amber-50 text-amber-600 dark:bg-amber-500/15 dark:text-amber-300',
        SupportTicket::STATUS_IN_PROGRESS => 'bg-sky-50 text-sky-600 dark:bg-sky-500/15 dark:text-sky-300',
        SupportTicket::STATUS_CLOSED => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-300',
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

            <div class="relative mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
                <section class="mb-7 flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="mb-4 inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700 dark:border-blue-400/20 dark:bg-blue-500/10 dark:text-blue-200">
                            Поддержка
                        </p>

                        <h1 class="text-3xl font-black tracking-tight text-slate-950 sm:text-4xl dark:text-white">
                            {{ $labels['heading'] }}
                        </h1>

                        <p class="mt-3 max-w-2xl text-sm font-semibold text-slate-500 sm:text-base dark:text-slate-300">
                            {{ $subtitle }}
                        </p>
                    </div>

                    <a href="{{ route('support-tickets.create') }}"
                       class="inline-flex items-center justify-center rounded-2xl bg-blue-600 px-6 py-3 text-sm font-black text-white shadow-lg shadow-blue-600/20 transition hover:-translate-y-0.5 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-400">
                        {{ $labels['create'] }}
                    </a>
                </section>

                @if(session('success'))
                    <div class="mb-6 rounded-[1.4rem] border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-black text-emerald-700 dark:border-emerald-400/20 dark:bg-emerald-500/10 dark:text-emerald-200">
                        {{ session('success') }}
                    </div>
                @endif

                <section class="overflow-hidden rounded-[1.7rem] border border-white bg-white shadow-sm shadow-slate-200/70 dark:border-white/10 dark:bg-white/[0.04] dark:shadow-none">
                    <div class="grid gap-0 divide-y divide-slate-100 dark:divide-white/10">
                        @forelse($tickets as $ticket)
                            <article class="p-5 transition hover:bg-slate-50 dark:hover:bg-white/[0.03]">
                                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                    <div class="min-w-0 flex-1">
                                        <div class="mb-3 flex flex-wrap items-center gap-2">
                                            <span class="rounded-full px-3 py-1 text-xs font-black {{ $typeClasses[$ticket->type] }}">
                                                {{ $labels[$ticket->type] }}
                                            </span>

                                            <span class="rounded-full px-3 py-1 text-xs font-black {{ $statusClasses[$ticket->status] }}">
                                                {{ $labels[$ticket->status] }}
                                            </span>
                                        </div>

                                        <h2 class="text-xl font-black text-slate-950 dark:text-white">
                                            {{ $ticket->subject }}
                                        </h2>

                                        <p class="mt-2 text-sm font-semibold leading-7 text-slate-600 dark:text-slate-300">
                                            {{ \Illuminate\Support\Str::limit($ticket->message, 220) }}
                                        </p>

                                        <div class="mt-4 grid grid-cols-1 gap-2 text-sm font-semibold text-slate-500 md:grid-cols-2 dark:text-slate-400">
                                            <p>
                                                {{ $labels['author'] }}:
                                                <span class="font-black text-slate-800 dark:text-slate-200">{{ $ticket->user?->name }}</span>
                                            </p>

                                            <p>
                                                {{ $labels['course'] }}:
                                                <span class="font-black text-slate-800 dark:text-slate-200">{{ $ticket->course?->title ?? $labels['no_course'] }}</span>
                                            </p>

                                            <p>
                                                {{ $labels['teacher'] }}:
                                                <span class="font-black text-slate-800 dark:text-slate-200">{{ $ticket->assignedTeacher?->name ?? $labels['not_assigned'] }}</span>
                                            </p>

                                            <p>
                                                {{ $labels['date'] }}:
                                                <span class="font-black text-slate-800 dark:text-slate-200">{{ $ticket->created_at->format('d.m.Y H:i') }}</span>
                                            </p>
                                        </div>
                                    </div>

                                    <div class="shrink-0">
                                        <a href="{{ route('support-tickets.show', $ticket) }}"
                                           class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-4 py-2 text-sm font-black text-white shadow-md shadow-blue-600/20 transition hover:-translate-y-0.5 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-400">
                                            {{ $labels['open'] }}
                                        </a>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <div class="px-5 py-12 text-center">
                                <div class="mx-auto mb-5 flex h-20 w-20 items-center justify-center rounded-[1.7rem] bg-blue-50 text-4xl dark:bg-blue-500/15">
                                    🎫
                                </div>

                                <h2 class="text-2xl font-black text-slate-950 dark:text-white">
                                    {{ $labels['empty'] }}
                                </h2>
                            </div>
                        @endforelse
                    </div>
                </section>
            </div>
        </div>
    </div>
</x-app-layout>