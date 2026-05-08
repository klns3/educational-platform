@php
    $labels = [
        'page' => 'Материал',
        'course' => 'Курс',
        'author' => 'Автор',
        'unknown_author' => 'Не указан',
        'images' => 'Иллюстрации',
        'back' => 'Назад к материалам',
        'edit' => 'Изменить',
        'draft' => 'Черновик',
        'published' => 'Опубликован',
        'empty' => 'Содержимое материала отсутствует',
    ];
@endphp

<x-app-layout>
    <style>
        .material-body h1,
        .material-body h2,
        .material-body h3 {
            color: #0f172a;
            font-weight: 800;
            line-height: 1.2;
            margin-top: 1.5rem;
            margin-bottom: 0.75rem;
        }

        .material-body h1 { font-size: 1.875rem; }
        .material-body h2 { font-size: 1.5rem; }
        .material-body h3 { font-size: 1.25rem; }

        .material-body p,
        .material-body li,
        .material-body blockquote {
            color: #475569;
            line-height: 1.8;
        }

        .material-body p,
        .material-body ul,
        .material-body ol,
        .material-body blockquote {
            margin-bottom: 1rem;
        }

        .material-body ul,
        .material-body ol {
            padding-left: 1.5rem;
        }

        .material-body ul { list-style: disc; }
        .material-body ol { list-style: decimal; }

        .material-body a {
            color: #2563eb;
            text-decoration: underline;
        }

        .material-body strong,
        .material-body b {
            color: #0f172a;
            font-weight: 800;
        }

        .material-body blockquote {
            border-left: 4px solid #4f46e5;
            padding-left: 1rem;
            color: #64748b;
        }

        html.dark .material-body h1,
        html.dark .material-body h2,
        html.dark .material-body h3,
        html.dark .material-body strong,
        html.dark .material-body b {
            color: #f8fafc;
        }

        html.dark .material-body p,
        html.dark .material-body li,
        html.dark .material-body blockquote {
            color: #cbd5e1;
        }

        html.dark .material-body a {
            color: #93c5fd;
        }

        html.dark .material-body blockquote {
            border-left-color: #6366f1;
            color: #94a3b8;
        }
    </style>

    <div class="min-h-screen bg-[#f6f8fc] text-slate-950 transition-colors duration-300 dark:bg-[#07111f] dark:text-white">
        <div class="relative overflow-hidden">
            <div class="pointer-events-none absolute inset-0 hidden dark:block">
                <div class="absolute left-[16%] top-0 h-80 w-80 rounded-full bg-blue-600/20 blur-3xl"></div>
                <div class="absolute right-0 top-10 h-96 w-96 rounded-full bg-violet-600/20 blur-3xl"></div>
                <div class="absolute bottom-0 left-1/2 h-72 w-72 rounded-full bg-cyan-500/10 blur-3xl"></div>
            </div>

            <div class="relative mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
                <section class="mb-7 flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p class="mb-4 inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700 dark:border-blue-400/20 dark:bg-blue-500/10 dark:text-blue-200">
                            {{ $labels['page'] }}
                        </p>

                        <h1 class="text-3xl font-black tracking-tight text-slate-950 sm:text-4xl dark:text-white">
                            {{ $material->title }}
                        </h1>

                        <p class="mt-3 max-w-3xl text-sm font-semibold text-slate-500 sm:text-base dark:text-slate-300">
                            {{ $labels['course'] }}: {{ $material->course->title }}
                            <span class="mx-2">|</span>
                            {{ $labels['author'] }}: {{ $material->author->name ?? $labels['unknown_author'] }}
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('materials.pdf', $material) }}"
                           class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-6 py-3 text-sm font-black text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:border-blue-400 hover:text-blue-600 dark:border-white/10 dark:bg-white/[0.04] dark:text-slate-200 dark:hover:border-blue-400">
                            PDF
                        </a>

                        @if(auth()->user()->role === 'admin' || auth()->id() === $material->author_id)
                            <a href="{{ route('materials.edit', $material) }}"
                               class="inline-flex items-center justify-center rounded-2xl bg-blue-600 px-6 py-3 text-sm font-black text-white shadow-lg shadow-blue-600/20 transition hover:-translate-y-0.5 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-400">
                                {{ $labels['edit'] }}
                            </a>
                        @endif
                    </div>
                </section>

                <section class="overflow-hidden rounded-[1.7rem] border border-white bg-white shadow-sm shadow-slate-200/70 dark:border-white/10 dark:bg-white/[0.04] dark:shadow-none">
                    <div class="border-b border-slate-100 px-6 py-5 dark:border-white/10">
                        <div class="flex flex-wrap items-center gap-2">
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
                    </div>

                    <div class="grid gap-8 p-6">
                        @if(!empty($material->image_urls))
                            <section>
                                <p class="mb-4 text-sm font-black uppercase tracking-[0.16em] text-slate-400 dark:text-slate-500">
                                    {{ $labels['images'] }}
                                </p>

                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    @foreach($material->image_urls as $imageUrl)
                                        <img src="{{ $imageUrl }}"
                                             alt="material image"
                                             class="h-72 w-full rounded-[1.4rem] border border-slate-200 object-cover shadow-sm shadow-slate-200/60 dark:border-white/10">
                                    @endforeach
                                </div>
                            </section>
                        @endif

                        <section class="rounded-[1.4rem] border border-slate-200 bg-slate-50 p-5 dark:border-white/10 dark:bg-white/[0.03]">
                            @if(trim(strip_tags((string) $material->rendered_content)) !== '')
                                <div class="material-body">
                                    {!! $material->rendered_content !!}
                                </div>
                            @else
                                <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">
                                    {{ $labels['empty'] }}
                                </p>
                            @endif
                        </section>

                        @if($material->has_file)
                            <section class="rounded-[1.4rem] border border-slate-200 bg-slate-50 p-5 dark:border-white/10 dark:bg-white/[0.03]">
                                <p class="mb-4 text-sm font-black uppercase tracking-[0.16em] text-slate-400 dark:text-slate-500">
                                    Файл материала
                                </p>

                                <a href="{{ route('materials.file', $material) }}"
                                   class="flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-4 transition hover:border-blue-400 hover:text-blue-600 sm:flex-row sm:items-center sm:justify-between dark:border-white/10 dark:bg-white/[0.04] dark:hover:border-blue-400">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-black text-slate-800 dark:text-slate-100">
                                            {{ $material->file_original_name }}
                                        </p>
                                        @if($material->formatted_file_size)
                                            <p class="mt-1 text-xs font-semibold text-slate-500 dark:text-slate-400">
                                                {{ $material->formatted_file_size }}
                                            </p>
                                        @endif
                                    </div>

                                    <span class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-4 py-2 text-sm font-black text-white">
                                        Скачать
                                    </span>
                                </a>
                            </section>
                        @endif

                        <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center">
                            <a href="{{ route('materials.index', $material->course) }}"
                               class="inline-flex items-center justify-center rounded-2xl border border-slate-200 px-5 py-3 text-sm font-black text-slate-700 transition hover:border-blue-400 hover:text-blue-600 dark:border-white/10 dark:text-slate-200">
                                {{ $labels['back'] }}
                            </a>

                            <a href="{{ route('materials.pdf', $material) }}"
                               class="inline-flex items-center justify-center rounded-2xl border border-slate-200 px-5 py-3 text-sm font-black text-slate-700 transition hover:border-blue-400 hover:text-blue-600 dark:border-white/10 dark:text-slate-200">
                                PDF
                            </a>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</x-app-layout>
