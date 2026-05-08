@php
    $labels = [
        'title' => 'Название курса',
        'description' => 'Описание',
        'cover' => 'Обложка курса',
        'save' => 'Обновить курс',
        'back' => 'Назад',
        'heading' => 'Редактирование курса',
        'subtitle' => 'Измените название, описание и визуальную обложку курса.',
        'cover_hint' => 'Новый файл заменит текущую обложку. Поддерживаются JPG, PNG и WEBP.',
        'current_cover' => 'Текущая обложка',
        'new_cover' => 'Новая обложка',
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

            <div class="relative mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
                <section class="mb-7">
                    <p class="mb-4 inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700 dark:border-blue-400/20 dark:bg-blue-500/10 dark:text-blue-200">
                        Курсы
                    </p>

                    <h1 class="text-3xl font-black tracking-tight text-slate-950 sm:text-4xl dark:text-white">
                        {{ $labels['heading'] }}
                    </h1>

                    <p class="mt-3 max-w-2xl text-sm font-semibold text-slate-500 sm:text-base dark:text-slate-300">
                        {{ $labels['subtitle'] }}
                    </p>
                </section>

                <form action="{{ route('courses.update', $course) }}"
                      method="POST"
                      enctype="multipart/form-data"
                      class="overflow-hidden rounded-[1.7rem] border border-white bg-white shadow-sm shadow-slate-200/70 dark:border-white/10 dark:bg-white/[0.04] dark:shadow-none">
                    @csrf
                    @method('PUT')

                    <div class="border-b border-slate-100 px-6 py-5 dark:border-white/10">
                        <h2 class="text-xl font-black text-slate-950 dark:text-white">
                            Основная информация
                        </h2>
                    </div>

                    <div class="grid gap-6 p-6">
                        <div>
                            <label class="mb-2 block text-sm font-black text-slate-700 dark:text-slate-200">
                                {{ $labels['title'] }}
                            </label>

                            <input type="text"
                                   name="title"
                                   value="{{ old('title', $course->title) }}"
                                   class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-950 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-4 focus:ring-blue-500/10 dark:border-white/10 dark:bg-white/[0.04] dark:text-white dark:focus:border-blue-400 dark:focus:bg-white/[0.07]">

                            @error('title')
                                <p class="mt-2 text-sm font-bold text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-black text-slate-700 dark:text-slate-200">
                                {{ $labels['description'] }}
                            </label>

                            <textarea name="description"
                                      rows="6"
                                      class="w-full resize-none rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-950 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-4 focus:ring-blue-500/10 dark:border-white/10 dark:bg-white/[0.04] dark:text-white dark:focus:border-blue-400 dark:focus:bg-white/[0.07]">{{ old('description', $course->description) }}</textarea>

                            @error('description')
                                <p class="mt-2 text-sm font-bold text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="rounded-[1.4rem] border border-slate-200 bg-slate-50 p-5 dark:border-white/10 dark:bg-white/[0.03]">
                            <div class="mb-4 flex items-center justify-between gap-4">
                                <div>
                                    <p class="text-sm font-black text-slate-700 dark:text-slate-200">
                                        {{ $labels['cover'] }}
                                    </p>
                                    <p class="mt-1 text-xs font-semibold text-slate-500 dark:text-slate-400">
                                        {{ $labels['cover_hint'] }}
                                    </p>
                                </div>

                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-blue-50 text-xl dark:bg-blue-500/15">
                                    🖼
                                </div>
                            </div>

                            @if($course->cover_url)
                                <div class="mb-5">
                                    <p class="mb-2 text-xs font-black uppercase tracking-wide text-slate-400 dark:text-slate-500">
                                        {{ $labels['current_cover'] }}
                                    </p>

                                    <img src="{{ $course->cover_url }}"
                                         alt="{{ $course->title }}"
                                         class="h-52 w-full rounded-[1.3rem] border border-slate-200 object-cover shadow-sm dark:border-white/10">
                                </div>
                            @endif

                            <label class="block">
                                <span class="mb-2 block text-xs font-black uppercase tracking-wide text-slate-400 dark:text-slate-500">
                                    {{ $labels['new_cover'] }}
                                </span>

                                <input type="file"
                                       name="cover"
                                       accept="image/jpeg,image/png,image/webp"
                                       class="block w-full rounded-2xl border border-dashed border-slate-300 bg-white px-4 py-4 text-sm font-bold text-slate-500 file:mr-4 file:rounded-xl file:border-0 file:bg-blue-600 file:px-4 file:py-2 file:text-sm file:font-black file:text-white hover:border-blue-400 dark:border-white/10 dark:bg-white/[0.04] dark:text-slate-300 dark:file:bg-blue-500">
                            </label>

                            @error('cover')
                                <p class="mt-2 text-sm font-bold text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex flex-col gap-3 border-t border-slate-100 px-6 py-5 sm:flex-row sm:items-center sm:justify-between dark:border-white/10">
                        <a href="{{ route('courses.index') }}"
                           class="inline-flex items-center justify-center rounded-2xl border border-slate-200 px-5 py-3 text-sm font-black text-slate-700 transition hover:border-blue-400 hover:text-blue-600 dark:border-white/10 dark:text-slate-200">
                            {{ $labels['back'] }}
                        </a>

                        <button class="inline-flex items-center justify-center rounded-2xl bg-blue-600 px-6 py-3 text-sm font-black text-white shadow-lg shadow-blue-600/20 transition hover:-translate-y-0.5 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-400">
                            {{ $labels['save'] }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>