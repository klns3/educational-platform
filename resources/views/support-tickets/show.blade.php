@php
    use App\Models\SupportTicket;

    $labels = [
        'heading' => 'Заявка',
        'author' => 'Автор',
        'course' => 'Курс',
        'teacher' => 'Ответственный',
        'status' => 'Статус',
        'message' => 'Описание',
        'date' => 'Создано',
        'update_status' => 'Изменить статус',
        'save' => 'Сохранить',
        'back' => 'К списку заявок',
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
@endphp

<x-app-layout>
    <div class="min-h-screen bg-[#f6f8fc] text-slate-950 transition-colors duration-300 dark:bg-[#07111f] dark:text-white">
        <div class="relative overflow-hidden">

            <div class="relative mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
                <section class="mb-7 flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="mb-4 inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700 dark:border-blue-400/20 dark:bg-blue-500/10 dark:text-blue-200">
                            Поддержка
                        </p>

                        <h1 class="text-3xl font-black tracking-tight text-slate-950 sm:text-4xl dark:text-white">
                            {{ $labels['heading'] }}
                        </h1>

                        <p class="mt-3 max-w-2xl text-sm font-semibold text-slate-500 sm:text-base dark:text-slate-300">
                            {{ $ticket->subject }}
                        </p>
                    </div>

                    <a href="{{ route('support-tickets.index') }}"
                       class="inline-flex items-center justify-center rounded-2xl border border-slate-200 px-5 py-3 text-sm font-black text-slate-700 transition hover:border-blue-400 hover:text-blue-600 dark:border-white/10 dark:text-slate-200">
                        {{ $labels['back'] }}
                    </a>
                </section>

                @if(session('success'))
                    <div class="mb-6 rounded-[1.4rem] border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-black text-emerald-700 dark:border-emerald-400/20 dark:bg-emerald-500/10 dark:text-emerald-200">
                        {{ session('success') }}
                    </div>
                @endif

                <section class="overflow-hidden rounded-[1.7rem] border border-white bg-white shadow-sm shadow-slate-200/70 dark:border-white/10 dark:bg-white/[0.04] dark:shadow-none">
                    <div class="border-b border-slate-100 px-6 py-5 dark:border-white/10">
                        <div class="mb-3 flex flex-wrap gap-2">
                            <span class="rounded-full px-3 py-1 text-xs font-black {{ $typeClasses[$ticket->type] }}">
                                {{ $labels[$ticket->type] }}
                            </span>

                            <span class="rounded-full px-3 py-1 text-xs font-black {{ $statusClasses[$ticket->status] }}">
                                {{ $labels[$ticket->status] }}
                            </span>
                        </div>

                        <h2 class="text-2xl font-black text-slate-950 dark:text-white">
                            {{ $ticket->subject }}
                        </h2>
                    </div>

                    <div class="grid gap-6 p-6">
                        <div class="grid grid-cols-1 gap-4 rounded-[1.4rem] border border-slate-200 bg-slate-50 p-5 text-sm md:grid-cols-2 dark:border-white/10 dark:bg-white/[0.03]">
                            <p class="font-semibold text-slate-500 dark:text-slate-400">
                                {{ $labels['author'] }}:
                                <span class="font-black text-slate-900 dark:text-slate-100">{{ $ticket->user?->name }}</span>
                            </p>

                            <p class="font-semibold text-slate-500 dark:text-slate-400">
                                {{ $labels['course'] }}:
                                <span class="font-black text-slate-900 dark:text-slate-100">{{ $ticket->course?->title ?? $labels['no_course'] }}</span>
                            </p>

                            <p class="font-semibold text-slate-500 dark:text-slate-400">
                                {{ $labels['teacher'] }}:
                                <span class="font-black text-slate-900 dark:text-slate-100">{{ $ticket->assignedTeacher?->name ?? $labels['not_assigned'] }}</span>
                            </p>

                            <p class="font-semibold text-slate-500 dark:text-slate-400">
                                {{ $labels['date'] }}:
                                <span class="font-black text-slate-900 dark:text-slate-100">{{ $ticket->created_at->format('d.m.Y H:i') }}</span>
                            </p>
                        </div>

                        <div>
                            <h2 class="mb-3 text-xl font-black text-slate-950 dark:text-white">
                                {{ $labels['message'] }}
                            </h2>

                            <div class="whitespace-pre-line rounded-[1.4rem] border border-slate-200 bg-slate-50 p-5 text-sm font-semibold leading-7 text-slate-600 dark:border-white/10 dark:bg-white/[0.03] dark:text-slate-300">
                                {{ $ticket->message }}
                            </div>
                        </div>

                        @if($canManageTicket)
                            <div class="rounded-[1.4rem] border border-blue-200 bg-blue-50 p-5 dark:border-blue-400/20 dark:bg-blue-500/10">
                                <h2 class="mb-4 text-xl font-black text-slate-950 dark:text-white">
                                    {{ $labels['update_status'] }}
                                </h2>

                                <form method="POST" action="{{ route('support-tickets.update', $ticket) }}" class="flex flex-col gap-3 sm:flex-row sm:items-end">
                                    @csrf
                                    @method('PATCH')

                                    <div class="min-w-56 flex-1">
                                        <label for="status" class="mb-2 block text-sm font-black text-slate-700 dark:text-slate-200">
                                            {{ $labels['status'] }}
                                        </label>

                                        <select id="status"
                                                name="status"
                                                class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-950 outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-500/10 dark:border-white/10 dark:bg-white/[0.05] dark:text-white">
                                            <option value="{{ SupportTicket::STATUS_NEW }}" @selected($ticket->status === SupportTicket::STATUS_NEW)>
                                                {{ $labels['new'] }}
                                            </option>
                                            <option value="{{ SupportTicket::STATUS_IN_PROGRESS }}" @selected($ticket->status === SupportTicket::STATUS_IN_PROGRESS)>
                                                {{ $labels['in_progress'] }}
                                            </option>
                                            <option value="{{ SupportTicket::STATUS_CLOSED }}" @selected($ticket->status === SupportTicket::STATUS_CLOSED)>
                                                {{ $labels['closed'] }}
                                            </option>
                                        </select>
                                    </div>

                                    <button class="inline-flex items-center justify-center rounded-2xl bg-blue-600 px-6 py-3 text-sm font-black text-white shadow-lg shadow-blue-600/20 transition hover:-translate-y-0.5 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-400">
                                        {{ $labels['save'] }}
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                </section>
            </div>
        </div>
    </div>
</x-app-layout>