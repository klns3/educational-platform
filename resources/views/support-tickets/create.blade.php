@php
    use App\Models\SupportTicket;

    $labels = [
        'heading' => 'Новая заявка',
        'subtitle' => 'Опишите проблему или вопрос, и мы покажем его нужному человеку.',
        'type' => 'Тип обращения',
        'subject' => 'Тема',
        'course' => 'Курс',
        'message' => 'Описание',
        'create' => 'Создать заявку',
        'back' => 'Назад',
        'type_help' => 'Для вопроса по курсу и заявки преподавателю нужно выбрать курс.',
        'course_placeholder' => 'Выберите курс',
        'subject_placeholder' => 'Кратко опишите тему',
        'message_placeholder' => 'Опишите ситуацию как можно подробнее',
        'technical_issue' => 'Техническая проблема',
        'course_question' => 'Вопрос по курсу',
        'teacher_request' => 'Заявка преподавателю',
    ];
@endphp

<x-app-layout>
    <div class="min-h-screen bg-[#f6f8fc] text-slate-950 transition-colors duration-300 dark:bg-[#07111f] dark:text-white">
        <div class="relative overflow-hidden">

            <div class="relative mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
                <section class="mb-7">
                    <p class="mb-4 inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700 dark:border-blue-400/20 dark:bg-blue-500/10 dark:text-blue-200">
                        Поддержка
                    </p>

                    <h1 class="text-3xl font-black tracking-tight text-slate-950 sm:text-4xl dark:text-white">
                        {{ $labels['heading'] }}
                    </h1>

                    <p class="mt-3 max-w-2xl text-sm font-semibold text-slate-500 sm:text-base dark:text-slate-300">
                        {{ $labels['subtitle'] }}
                    </p>
                </section>

                @if($errors->any())
                    <div class="mb-6 rounded-[1.4rem] border border-red-200 bg-red-50 px-5 py-4 text-sm font-black text-red-700 dark:border-red-400/20 dark:bg-red-500/10 dark:text-red-200">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST"
                      action="{{ route('support-tickets.store') }}"
                      class="overflow-hidden rounded-[1.7rem] border border-white bg-white shadow-sm shadow-slate-200/70 dark:border-white/10 dark:bg-white/[0.04] dark:shadow-none">
                    @csrf

                    <div class="border-b border-slate-100 px-6 py-5 dark:border-white/10">
                        <h2 class="text-xl font-black text-slate-950 dark:text-white">
                            Данные обращения
                        </h2>
                    </div>

                    <div class="grid gap-6 p-6">
                        <div>
                            <label for="type" class="mb-2 block text-sm font-black text-slate-700 dark:text-slate-200">
                                {{ $labels['type'] }}
                            </label>

                            <select id="type"
                                    name="type"
                                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-950 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-4 focus:ring-blue-500/10 dark:border-white/10 dark:bg-white/[0.04] dark:text-white">
                                <option value="{{ SupportTicket::TYPE_TECHNICAL_ISSUE }}" @selected(old('type') === SupportTicket::TYPE_TECHNICAL_ISSUE)>
                                    {{ $labels['technical_issue'] }}
                                </option>
                                <option value="{{ SupportTicket::TYPE_COURSE_QUESTION }}" @selected(old('type') === SupportTicket::TYPE_COURSE_QUESTION)>
                                    {{ $labels['course_question'] }}
                                </option>
                                <option value="{{ SupportTicket::TYPE_TEACHER_REQUEST }}" @selected(old('type') === SupportTicket::TYPE_TEACHER_REQUEST)>
                                    {{ $labels['teacher_request'] }}
                                </option>
                            </select>

                            <p class="mt-2 text-xs font-semibold text-slate-500 dark:text-slate-400">
                                {{ $labels['type_help'] }}
                            </p>
                        </div>

                        <div>
                            <label for="subject" class="mb-2 block text-sm font-black text-slate-700 dark:text-slate-200">
                                {{ $labels['subject'] }}
                            </label>

                            <input id="subject"
                                   type="text"
                                   name="subject"
                                   value="{{ old('subject') }}"
                                   placeholder="{{ $labels['subject_placeholder'] }}"
                                   class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-950 outline-none transition placeholder:text-slate-400 focus:border-blue-400 focus:bg-white focus:ring-4 focus:ring-blue-500/10 dark:border-white/10 dark:bg-white/[0.04] dark:text-white">
                        </div>

                        <div>
                            <label for="course_id" class="mb-2 block text-sm font-black text-slate-700 dark:text-slate-200">
                                {{ $labels['course'] }}
                            </label>

                            <select id="course_id"
                                    name="course_id"
                                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-950 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-4 focus:ring-blue-500/10 dark:border-white/10 dark:bg-white/[0.04] dark:text-white">
                                <option value="">{{ $labels['course_placeholder'] }}</option>

                                @foreach($courses as $course)
                                    <option value="{{ $course->id }}" @selected((int) old('course_id') === $course->id)>
                                        {{ $course->title }}{{ $course->teacher ? ' (' . $course->teacher->name . ')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="message" class="mb-2 block text-sm font-black text-slate-700 dark:text-slate-200">
                                {{ $labels['message'] }}
                            </label>

                            <textarea id="message"
                                      name="message"
                                      rows="8"
                                      placeholder="{{ $labels['message_placeholder'] }}"
                                      class="w-full resize-none rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-950 outline-none transition placeholder:text-slate-400 focus:border-blue-400 focus:bg-white focus:ring-4 focus:ring-blue-500/10 dark:border-white/10 dark:bg-white/[0.04] dark:text-white">{{ old('message') }}</textarea>
                        </div>
                    </div>

                    <div class="flex flex-col gap-3 border-t border-slate-100 px-6 py-5 sm:flex-row sm:items-center sm:justify-between dark:border-white/10">
                        <a href="{{ route('support-tickets.index') }}"
                           class="inline-flex items-center justify-center rounded-2xl border border-slate-200 px-5 py-3 text-sm font-black text-slate-700 transition hover:border-blue-400 hover:text-blue-600 dark:border-white/10 dark:text-slate-200">
                            {{ $labels['back'] }}
                        </a>

                        <button class="inline-flex items-center justify-center rounded-2xl bg-blue-600 px-6 py-3 text-sm font-black text-white shadow-lg shadow-blue-600/20 transition hover:-translate-y-0.5 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-400">
                            {{ $labels['create'] }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>