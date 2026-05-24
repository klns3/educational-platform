<x-app-layout>
    <div class="min-h-screen bg-[#f6f8fc] text-slate-950 transition-colors duration-300 dark:bg-[#07111f] dark:text-white">
        <div class="relative overflow-hidden">

            <div class="relative mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
                <section class="mb-7 flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="mb-4 inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700 dark:border-blue-400/20 dark:bg-blue-500/10 dark:text-blue-200">
                            Тесты
                        </p>

                        <h1 class="text-3xl font-black tracking-tight text-slate-950 sm:text-4xl dark:text-white">
                            Тесты курса
                        </h1>

                        <p class="mt-3 max-w-2xl text-sm font-semibold text-slate-500 sm:text-base dark:text-slate-300">
                            {{ $course->title }}
                        </p>
                    </div>

                    @if(in_array(auth()->user()->role, ['admin', 'teacher']))
                        <a href="{{ route('tests.create', $course) }}"
                           class="inline-flex items-center justify-center rounded-2xl bg-blue-600 px-6 py-3 text-sm font-black text-white shadow-lg shadow-blue-600/20 transition hover:-translate-y-0.5 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-400">
                            Добавить тест
                        </a>
                    @endif
                </section>

                @if(session('success'))
                    <div class="mb-6 rounded-[1.4rem] border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-black text-emerald-700 dark:border-emerald-400/20 dark:bg-emerald-500/10 dark:text-emerald-200">
                        {{ session('success') }}
                    </div>
                @endif

                <section class="overflow-hidden rounded-[1.7rem] border border-white bg-white shadow-sm shadow-slate-200/70 dark:border-white/10 dark:bg-white/[0.04] dark:shadow-none">
                    <div class="grid divide-y divide-slate-100 dark:divide-white/10">
                        @forelse($tests as $test)
                            <article class="p-5 transition hover:bg-slate-50 dark:hover:bg-white/[0.03]">
                                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                    <div class="min-w-0 flex-1">
                                        <div class="mb-3 flex flex-wrap items-center gap-2">
                                            <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-black text-blue-600 dark:bg-blue-500/15 dark:text-blue-300">
                                                {{ $test->is_published ? 'Опубликован' : 'Черновик' }}
                                            </span>

                                            <span class="rounded-full bg-slate-50 px-3 py-1 text-xs font-black text-slate-500 dark:bg-white/10 dark:text-slate-300">
                                                {{ $test->time_limit ?? 'без ограничения' }} мин.
                                            </span>

                                            <span class="rounded-full bg-violet-50 px-3 py-1 text-xs font-black text-violet-600 dark:bg-violet-500/15 dark:text-violet-300">
                                                Попытки: {{ $test->attempts_limit ?? 'без ограничения' }}
                                            </span>
                                        </div>

                                        <h2 class="text-xl font-black text-slate-950 dark:text-white">
                                            <a href="{{ route('tests.show', $test) }}" class="transition hover:text-blue-600 dark:hover:text-blue-300">
                                                {{ $test->title }}
                                            </a>
                                        </h2>

                                        <p class="mt-2 text-sm font-semibold leading-7 text-slate-600 dark:text-slate-300">
                                            {{ $test->description ?? 'Описание отсутствует' }}
                                        </p>
                                    </div>

                                    <div class="flex shrink-0 flex-wrap gap-2">
                                        <a href="{{ route('tests.show', $test) }}"
                                           class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-4 py-2 text-sm font-black text-white shadow-md shadow-blue-600/20 transition hover:-translate-y-0.5 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-400">
                                            Открыть
                                        </a>

                                        @if(in_array(auth()->user()->role, ['admin', 'teacher']))
                                            <a href="{{ route('tests.edit', $test) }}"
                                               class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-4 py-2 text-sm font-black text-slate-700 transition hover:border-blue-400 hover:text-blue-600 dark:border-white/10 dark:text-slate-200">
                                                Редактировать
                                            </a>

                                            <form action="{{ route('tests.destroy', $test) }}"
                                                  method="POST"
                                                  onsubmit="return confirm('Удалить тест?')">
                                                @csrf
                                                @method('DELETE')

                                                <button class="inline-flex items-center justify-center rounded-xl border border-red-200 bg-red-50 px-4 py-2 text-sm font-black text-red-600 transition hover:border-red-400 hover:bg-red-100 dark:border-red-400/20 dark:bg-red-500/10 dark:text-red-300 dark:hover:bg-red-500/15">
                                                    Удалить
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </article>
                        @empty
                            <div class="px-5 py-12 text-center">
                                <div class="mx-auto mb-5 flex h-20 w-20 items-center justify-center rounded-[1.7rem] bg-blue-50 text-4xl dark:bg-blue-500/15">
                                    🧪
                                </div>

                                <h2 class="text-2xl font-black text-slate-950 dark:text-white">
                                    Тестов пока нет
                                </h2>
                            </div>
                        @endforelse
                    </div>
                </section>

                <div class="mt-8">
                    <a href="{{ route('courses.show', $course) }}"
                       class="inline-flex items-center justify-center rounded-2xl border border-slate-200 px-5 py-3 text-sm font-black text-slate-700 transition hover:border-blue-400 hover:text-blue-600 dark:border-white/10 dark:text-slate-200">
                        ← Назад к курсу
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
