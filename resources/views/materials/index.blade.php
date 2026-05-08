@php
    $labels = [
        'heading' => 'Материалы курса',
        'add' => 'Добавить материал',
        'author' => 'Автор',
        'unknown_author' => 'Не указан',
        'open' => 'Открыть',
        'edit' => 'Изменить',
        'delete' => 'Удалить',
        'delete_confirm' => 'Удалить материал?',
        'back' => 'Назад к курсу',
        'draft' => 'Черновик',
        'published' => 'Опубликован',
        'empty' => 'Материалы пока не добавлены.',
        'images' => 'иллюстраций',
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

            <div class="relative mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
                <section class="mb-7 flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <p class="mb-4 inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700 dark:border-blue-400/20 dark:bg-blue-500/10 dark:text-blue-200">
                            Материалы
                        </p>

                        <h1 class="text-3xl font-black tracking-tight text-slate-950 sm:text-4xl dark:text-white">
                            {{ $labels['heading'] }}
                        </h1>

                        <p class="mt-3 max-w-2xl text-sm font-semibold text-slate-500 sm:text-base dark:text-slate-300">
                            {{ $course->title }}
                        </p>
                    </div>

                    @if(auth()->user()->role === 'admin' || auth()->user()->role === 'teacher')
                        <a href="{{ route('materials.create', $course) }}"
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

                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    @forelse($materials as $material)
                        <article class="group overflow-hidden rounded-[1.7rem] border border-white bg-white shadow-sm shadow-slate-200/70 transition hover:-translate-y-1 hover:shadow-xl hover:shadow-blue-100 dark:border-white/10 dark:bg-white/[0.04] dark:shadow-none dark:hover:bg-white/[0.06]">
                            @if(!empty($material->image_urls))
                                <img src="{{ $material->image_urls[0] }}"
                                     alt="material preview"
                                     class="h-56 w-full object-cover transition duration-300 group-hover:scale-[1.02]">
                            @else
                                <div class="flex h-56 items-center justify-center bg-gradient-to-br from-blue-600/15 via-violet-600/10 to-cyan-500/10 px-8 text-center">
                                    <span class="text-2xl font-black tracking-tight text-slate-300 dark:text-white/40">
                                        {{ $material->title }}
                                    </span>
                                </div>
                            @endif

                            <div class="p-6">
                                <div class="mb-4 flex flex-wrap items-center gap-3">
                                    <span class="rounded-full px-3 py-1 text-xs font-black {{ $material->is_published ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-300' : 'bg-amber-50 text-amber-600 dark:bg-amber-500/15 dark:text-amber-300' }}">
                                        {{ $material->is_published ? $labels['published'] : $labels['draft'] }}
                                    </span>

                                    @if(!empty($material->image_urls))
                                        <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-black text-blue-600 dark:bg-blue-500/15 dark:text-blue-300">
                                            {{ count($material->image_urls) }} {{ $labels['images'] }}
                                        </span>
                                    @endif

                                    @if($material->has_file)
                                        <span class="rounded-full bg-violet-50 px-3 py-1 text-xs font-black text-violet-600 dark:bg-violet-500/15 dark:text-violet-300">
                                            Файл
                                        </span>
                                    @endif
                                </div>

                                <h2 class="text-2xl font-black tracking-tight text-slate-950 dark:text-white">
                                    {{ $material->title }}
                                </h2>

                                <p class="mt-2 text-sm font-semibold text-slate-500 dark:text-slate-400">
                                    {{ $labels['author'] }}: {{ $material->author->name ?? $labels['unknown_author'] }}
                                </p>

                                <p class="mt-4 line-clamp-3 text-sm font-semibold leading-7 text-slate-600 dark:text-slate-300">
                                    {{ $material->excerpt }}
                                </p>

                                <div class="mt-6 flex flex-wrap gap-3">
                                    <a href="{{ route('materials.show', $material) }}"
                                       class="inline-flex items-center justify-center rounded-2xl bg-blue-600 px-5 py-3 text-sm font-black text-white shadow-lg shadow-blue-600/20 transition hover:-translate-y-0.5 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-400">
                                        {{ $labels['open'] }}
                                    </a>

                                    <a href="{{ route('materials.pdf', $material) }}"
                                       class="inline-flex items-center justify-center rounded-2xl border border-slate-200 px-5 py-3 text-sm font-black text-slate-700 transition hover:border-blue-400 hover:text-blue-600 dark:border-white/10 dark:text-slate-200">
                                        PDF
                                    </a>

                                    @if(auth()->user()->role === 'admin' || auth()->id() === $material->author_id)
                                        <a href="{{ route('materials.edit', $material) }}"
                                           class="inline-flex items-center justify-center rounded-2xl border border-amber-200 bg-amber-50 px-5 py-3 text-sm font-black text-amber-700 transition hover:border-amber-400 hover:bg-amber-100 dark:border-amber-400/20 dark:bg-amber-500/10 dark:text-amber-300 dark:hover:bg-amber-500/15">
                                            {{ $labels['edit'] }}
                                        </a>

                                        <form action="{{ route('materials.destroy', $material) }}"
                                              method="POST"
                                              onsubmit="return confirm('{{ $labels['delete_confirm'] }}')">
                                            @csrf
                                            @method('DELETE')

                                            <button class="inline-flex items-center justify-center rounded-2xl border border-red-200 bg-red-50 px-5 py-3 text-sm font-black text-red-600 transition hover:border-red-400 hover:bg-red-100 dark:border-red-400/20 dark:bg-red-500/10 dark:text-red-300 dark:hover:bg-red-500/15">
                                                {{ $labels['delete'] }}
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="rounded-[1.7rem] border border-dashed border-slate-300 bg-white px-8 py-16 text-center shadow-sm shadow-slate-200/70 lg:col-span-2 dark:border-white/10 dark:bg-white/[0.04] dark:shadow-none">
                            <p class="text-xl font-black text-slate-950 dark:text-white">
                                {{ $labels['empty'] }}
                            </p>

                            <p class="mt-2 text-sm font-semibold text-slate-500 dark:text-slate-400">
                                Здесь появятся лекции, конспекты и дополнительные файлы курса.
                            </p>
                        </div>
                    @endforelse
                </div>

                <div class="mt-8">
                    <a href="{{ route('courses.show', $course) }}"
                       class="inline-flex items-center justify-center rounded-2xl border border-slate-200 px-5 py-3 text-sm font-black text-slate-700 transition hover:border-blue-400 hover:text-blue-600 dark:border-white/10 dark:text-slate-200">
                        {{ $labels['back'] }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
