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
                        Тест
                    </p>

                    <h1 class="text-3xl font-black tracking-tight text-slate-950 sm:text-4xl dark:text-white">
                        {{ $test->title }}
                    </h1>

                    <p class="mt-3 max-w-2xl text-sm font-semibold text-slate-500 sm:text-base dark:text-slate-300">
                        {{ $test->description ?? 'Описание отсутствует' }}
                    </p>
                </section>

                @if(session('error'))
                    <div class="mb-6 rounded-[1.4rem] border border-red-200 bg-red-50 px-5 py-4 text-sm font-black text-red-700 dark:border-red-400/20 dark:bg-red-500/10 dark:text-red-200">
                        {{ session('error') }}
                    </div>
                @endif

                @if(session('success'))
                    <div class="mb-6 rounded-[1.4rem] border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-black text-emerald-700 dark:border-emerald-400/20 dark:bg-emerald-500/10 dark:text-emerald-200">
                        {{ session('success') }}
                    </div>
                @endif

                <section class="overflow-hidden rounded-[1.7rem] border border-white bg-white shadow-sm shadow-slate-200/70 dark:border-white/10 dark:bg-white/[0.04] dark:shadow-none">
                    <div class="border-b border-slate-100 px-6 py-5 dark:border-white/10">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-black text-blue-600 dark:bg-blue-500/15 dark:text-blue-300">
                                {{ $test->is_published ? 'Опубликован' : 'Черновик' }}
                            </span>

                            <span class="rounded-full bg-slate-50 px-3 py-1 text-xs font-black text-slate-500 dark:bg-white/10 dark:text-slate-300">
                                {{ $test->time_limit ? $test->time_limit . ' мин.' : 'Без ограничения' }}
                            </span>

                            <span class="rounded-full bg-violet-50 px-3 py-1 text-xs font-black text-violet-600 dark:bg-violet-500/15 dark:text-violet-300">
                                Попытки: {{ $test->attempts_limit ?? 'без ограничения' }}
                            </span>
                        </div>
                    </div>

                    <div class="grid gap-6 p-6">
                        <div class="grid grid-cols-1 gap-4 rounded-[1.4rem] border border-slate-200 bg-slate-50 p-5 text-sm md:grid-cols-2 dark:border-white/10 dark:bg-white/[0.03]">
                            <p class="font-semibold text-slate-500 dark:text-slate-400">
                                Курс:
                                <span class="font-black text-slate-900 dark:text-slate-100">{{ $test->course->title }}</span>
                            </p>

                            <p class="font-semibold text-slate-500 dark:text-slate-400">
                                Автор:
                                <span class="font-black text-slate-900 dark:text-slate-100">{{ $test->author->name ?? 'Не указан' }}</span>
                            </p>

                            <p class="font-semibold text-slate-500 dark:text-slate-400">
                                Время:
                                <span class="font-black text-slate-900 dark:text-slate-100">{{ $test->time_limit ? $test->time_limit . ' мин.' : 'без ограничения' }}</span>
                            </p>

                            <p class="font-semibold text-slate-500 dark:text-slate-400">
                                Статус:
                                <span class="font-black text-slate-900 dark:text-slate-100">{{ $test->is_published ? 'Опубликован' : 'Черновик' }}</span>
                            </p>
                        </div>

                        <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center">
                            @if(in_array(auth()->user()->role, ['admin', 'teacher']))
                                <a href="{{ route('questions.index', $test) }}"
                                   class="inline-flex items-center justify-center rounded-2xl bg-violet-600 px-6 py-3 text-sm font-black text-white shadow-lg shadow-violet-600/20 transition hover:-translate-y-0.5 hover:bg-violet-700 dark:bg-violet-500 dark:hover:bg-violet-400">
                                    Вопросы теста
                                </a>

                                <a href="{{ route('tests.edit', $test) }}"
                                   class="inline-flex items-center justify-center rounded-2xl bg-blue-600 px-6 py-3 text-sm font-black text-white shadow-lg shadow-blue-600/20 transition hover:-translate-y-0.5 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-400">
                                    Редактировать
                                </a>
                            @endif

                            @if(auth()->user()->role !== 'student' || $test->is_published)
                                <a href="{{ route('tests.take', $test) }}"
                                   class="inline-flex items-center justify-center rounded-2xl bg-emerald-600 px-6 py-3 text-sm font-black text-white shadow-lg shadow-emerald-600/20 transition hover:-translate-y-0.5 hover:bg-emerald-700 dark:bg-emerald-500 dark:hover:bg-emerald-400">
                                    Пройти тест
                                </a>
                            @endif

                            <a href="{{ route('tests.index', $test->course) }}"
                               class="inline-flex items-center justify-center rounded-2xl border border-slate-200 px-5 py-3 text-sm font-black text-slate-700 transition hover:border-blue-400 hover:text-blue-600 dark:border-white/10 dark:text-slate-200">
                                Назад
                            </a>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</x-app-layout>
