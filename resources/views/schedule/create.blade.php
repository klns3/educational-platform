@php
    use App\Models\ScheduleEvent;

    $labels = [
        'heading' => 'Новое событие расписания',
        'subtitle' => 'Выберите группу, время и параметры события',
        'title' => 'Название',
        'description' => 'Описание',
        'type' => 'Тип',
        'teacher' => 'Преподаватель',
        'group' => 'Группа',
        'course' => 'Курс',
        'location' => 'Место или ссылка',
        'starts_at' => 'Начало',
        'ends_at' => 'Окончание',
        'create' => 'Создать',
        'back' => 'Назад',
        'teacher_placeholder' => 'Выберите преподавателя',
        'group_placeholder' => 'Выберите группу',
        'course_placeholder' => 'Без привязки к курсу',
        'lesson' => 'Занятие',
        'consultation' => 'Консультация',
        'exam' => 'Экзамен',
        'other' => 'Другое',
    ];
@endphp

<x-app-layout>
    <div class="min-h-screen bg-[#f6f8fc] text-slate-950 transition-colors duration-300 dark:bg-[#07111f] dark:text-white">
        <div class="relative overflow-hidden">
            <div class="pointer-events-none absolute inset-0 hidden dark:block">
                <div class="absolute left-[16%] top-0 h-80 w-80 rounded-full bg-blue-600/20 blur-3xl"></div>
                <div class="absolute right-0 top-10 h-96 w-96 rounded-full bg-violet-600/20 blur-3xl"></div>
                <div class="absolute bottom-0 left-1/2 h-72 w-72 rounded-full bg-cyan-500/10 blur-3xl"></div>
            </div>

            <div class="relative mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
                <section class="mb-7">
                    <p class="mb-4 inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700 dark:border-blue-400/20 dark:bg-blue-500/10 dark:text-blue-200">
                        Расписание
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
                      action="{{ route('schedule.store') }}"
                      class="overflow-hidden rounded-[1.7rem] border border-white bg-white shadow-sm shadow-slate-200/70 dark:border-white/10 dark:bg-white/[0.04] dark:shadow-none">
                    @csrf

                    <div class="border-b border-slate-100 px-6 py-5 dark:border-white/10">
                        <h2 class="text-xl font-black text-slate-950 dark:text-white">
                            Данные события
                        </h2>
                    </div>

                    <div class="grid gap-6 p-6">
                        <div>
                            <label for="title" class="mb-2 block text-sm font-black text-slate-700 dark:text-slate-200">
                                {{ $labels['title'] }}
                            </label>

                            <input id="title"
                                   type="text"
                                   name="title"
                                   value="{{ old('title') }}"
                                   class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-950 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-4 focus:ring-blue-500/10 dark:border-white/10 dark:bg-white/[0.04] dark:text-white">
                        </div>

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <label for="type" class="mb-2 block text-sm font-black text-slate-700 dark:text-slate-200">
                                    {{ $labels['type'] }}
                                </label>

                                <select id="type"
                                        name="type"
                                        class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-950 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-4 focus:ring-blue-500/10 dark:border-white/10 dark:bg-white/[0.04] dark:text-white">
                                    @foreach([ScheduleEvent::TYPE_LESSON, ScheduleEvent::TYPE_CONSULTATION, ScheduleEvent::TYPE_EXAM, ScheduleEvent::TYPE_OTHER] as $type)
                                        <option value="{{ $type }}" @selected(old('type', ScheduleEvent::TYPE_LESSON) === $type)>
                                            {{ $labels[$type] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            @if(auth()->user()->role === 'admin')
                                <div>
                                    <label for="teacher_id" class="mb-2 block text-sm font-black text-slate-700 dark:text-slate-200">
                                        {{ $labels['teacher'] }}
                                    </label>

                                    <select id="teacher_id"
                                            name="teacher_id"
                                            class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-950 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-4 focus:ring-blue-500/10 dark:border-white/10 dark:bg-white/[0.04] dark:text-white">
                                        <option value="">{{ $labels['teacher_placeholder'] }}</option>

                                        @foreach($teachers as $teacher)
                                            <option value="{{ $teacher->id }}" @selected((int) old('teacher_id') === $teacher->id)>
                                                {{ $teacher->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @else
                                <div>
                                    <label for="location" class="mb-2 block text-sm font-black text-slate-700 dark:text-slate-200">
                                        {{ $labels['location'] }}
                                    </label>

                                    <input id="location"
                                           type="text"
                                           name="location"
                                           value="{{ old('location') }}"
                                           class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-950 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-4 focus:ring-blue-500/10 dark:border-white/10 dark:bg-white/[0.04] dark:text-white">
                                </div>
                            @endif
                        </div>

                        @if(auth()->user()->role === 'admin')
                            <div>
                                <label for="location" class="mb-2 block text-sm font-black text-slate-700 dark:text-slate-200">
                                    {{ $labels['location'] }}
                                </label>

                                <input id="location"
                                       type="text"
                                       name="location"
                                       value="{{ old('location') }}"
                                       class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-950 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-4 focus:ring-blue-500/10 dark:border-white/10 dark:bg-white/[0.04] dark:text-white">
                            </div>
                        @endif

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <label for="class_group_id" class="mb-2 block text-sm font-black text-slate-700 dark:text-slate-200">
                                    {{ $labels['group'] }}
                                </label>

                                <select id="class_group_id"
                                        name="class_group_id"
                                        class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-950 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-4 focus:ring-blue-500/10 dark:border-white/10 dark:bg-white/[0.04] dark:text-white">
                                    <option value="">{{ $labels['group_placeholder'] }}</option>

                                    @foreach($groups as $group)
                                        <option value="{{ $group->id }}" @selected((int) old('class_group_id') === $group->id)>
                                            {{ $group->name }}
                                        </option>
                                    @endforeach
                                </select>
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
                                            {{ $course->title }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <label for="starts_at" class="mb-2 block text-sm font-black text-slate-700 dark:text-slate-200">
                                    {{ $labels['starts_at'] }}
                                </label>

                                <input id="starts_at"
                                       type="datetime-local"
                                       name="starts_at"
                                       value="{{ old('starts_at') }}"
                                       class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-950 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-4 focus:ring-blue-500/10 dark:border-white/10 dark:bg-white/[0.04] dark:text-white">
                            </div>

                            <div>
                                <label for="ends_at" class="mb-2 block text-sm font-black text-slate-700 dark:text-slate-200">
                                    {{ $labels['ends_at'] }}
                                </label>

                                <input id="ends_at"
                                       type="datetime-local"
                                       name="ends_at"
                                       value="{{ old('ends_at') }}"
                                       class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-950 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-4 focus:ring-blue-500/10 dark:border-white/10 dark:bg-white/[0.04] dark:text-white">
                            </div>
                        </div>

                        <div>
                            <label for="description" class="mb-2 block text-sm font-black text-slate-700 dark:text-slate-200">
                                {{ $labels['description'] }}
                            </label>

                            <textarea id="description"
                                      name="description"
                                      rows="5"
                                      class="w-full resize-none rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-950 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-4 focus:ring-blue-500/10 dark:border-white/10 dark:bg-white/[0.04] dark:text-white">{{ old('description') }}</textarea>
                        </div>
                    </div>

                    <div class="flex flex-col gap-3 border-t border-slate-100 px-6 py-5 sm:flex-row sm:items-center sm:justify-between dark:border-white/10">
                        <a href="{{ route('schedule.index') }}"
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