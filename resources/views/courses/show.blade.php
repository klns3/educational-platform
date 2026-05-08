<x-app-layout>
    @php
        $user = auth()->user();
        $isStudent = $user->role === 'student';
        $canManage = $user->role === 'admin' || $user->id === $course->teacher_id;

        $progress = (int) ($course->progress_percent ?? 0);
        $availableTestsCount = (int) ($course->available_tests_count ?? 0);
        $completedTestsCount = (int) ($course->completed_tests_count ?? 0);
        $materialsCount = $course->materials?->count() ?? 0;
        $testsCount = $course->tests?->count() ?? 0;
    @endphp

    <div class="min-h-screen bg-[#f6f8fc] text-slate-950 transition-colors duration-300 dark:bg-[#07111f] dark:text-white">
        <div class="relative overflow-hidden">
            <div class="pointer-events-none absolute inset-0 hidden dark:block">
                <div class="absolute left-[18%] top-0 h-80 w-80 rounded-full bg-blue-600/20 blur-3xl"></div>
                <div class="absolute right-0 top-10 h-96 w-96 rounded-full bg-violet-600/20 blur-3xl"></div>
                <div class="absolute bottom-0 left-1/2 h-72 w-72 rounded-full bg-cyan-500/10 blur-3xl"></div>
            </div>

            <div class="relative mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
                <div class="mb-6">
                    <a href="{{ route('courses.index') }}"
                       class="inline-flex items-center rounded-2xl border border-slate-200 bg-white/70 px-4 py-2 text-sm font-black text-slate-600 shadow-sm backdrop-blur-xl transition hover:border-blue-400 hover:text-blue-600 dark:border-white/10 dark:bg-white/[0.04] dark:text-slate-300">
                        ← Назад к курсам
                    </a>
                </div>

                <section class="mb-6 overflow-hidden rounded-[2rem] border border-white bg-white shadow-sm shadow-slate-200/70 dark:border-white/10 dark:bg-white/[0.04] dark:shadow-none">
                    <div class="grid gap-0 lg:grid-cols-[420px_1fr]">
                        <div class="relative min-h-[280px]">
                            @if($course->cover_url)
                                <img
                                    src="{{ $course->cover_url }}"
                                    alt="{{ $course->title }}"
                                    class="absolute inset-0 h-full w-full object-cover"
                                >
                                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/70 via-slate-950/20 to-transparent"></div>
                            @else
                                <div class="absolute inset-0 flex items-center justify-center bg-gradient-to-br from-blue-600 via-violet-600 to-cyan-500 px-8 text-center">
                                    <span class="text-4xl font-black leading-tight tracking-tight text-white/90">
                                        {{ $course->title }}
                                    </span>
                                </div>
                            @endif
                        </div>

                        <div class="p-6 sm:p-8">
                            <p class="mb-4 inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700 dark:border-blue-400/20 dark:bg-blue-500/10 dark:text-blue-200">
                                Карточка курса
                            </p>

                            <h1 class="text-3xl font-black tracking-tight text-slate-950 sm:text-4xl dark:text-white">
                                {{ $course->title }}
                            </h1>

                            <p class="mt-3 max-w-3xl text-sm font-semibold leading-7 text-slate-500 dark:text-slate-300">
                                {{ $course->description ?: 'Описание курса пока не добавлено.' }}
                            </p>

                            <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
                                <div class="rounded-2xl bg-slate-50 p-4 dark:bg-white/[0.04]">
                                    <p class="text-xs font-bold text-slate-500 dark:text-slate-400">Преподаватель</p>
                                    <p class="mt-1 truncate font-black text-slate-950 dark:text-white">
                                        {{ $course->teacher?->name ?? 'Не указан' }}
                                    </p>
                                </div>

                                <div class="rounded-2xl bg-slate-50 p-4 dark:bg-white/[0.04]">
                                    <p class="text-xs font-bold text-slate-500 dark:text-slate-400">Материалы</p>
                                    <p class="mt-1 text-2xl font-black text-slate-950 dark:text-white">{{ $materialsCount }}</p>
                                </div>

                                <div class="rounded-2xl bg-slate-50 p-4 dark:bg-white/[0.04]">
                                    <p class="text-xs font-bold text-slate-500 dark:text-slate-400">Тесты</p>
                                    <p class="mt-1 text-2xl font-black text-slate-950 dark:text-white">{{ $testsCount }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="mb-6 grid grid-cols-1 gap-5 lg:grid-cols-2">
                    <a href="{{ route('materials.index', $course) }}"
                       class="group overflow-hidden rounded-[2rem] border border-white bg-white p-7 shadow-sm shadow-slate-200/70 transition hover:-translate-y-1 hover:shadow-xl hover:shadow-blue-100 dark:border-white/10 dark:bg-white/[0.04] dark:shadow-none">
                        <div class="flex items-start justify-between gap-6">
                            <div>
                                <div class="mb-5 flex h-16 w-16 items-center justify-center rounded-3xl bg-blue-50 text-3xl text-blue-600 shadow-inner dark:bg-blue-500/15 dark:text-blue-300">
                                    📄
                                </div>

                                <h2 class="text-3xl font-black text-slate-950 dark:text-white">
                                    Материалы курса
                                </h2>

                                <p class="mt-3 max-w-md text-sm font-semibold leading-6 text-slate-500 dark:text-slate-400">
                                    Открыть лекции, изображения и учебные материалы по этому курсу.
                                </p>
                            </div>

                            <div class="hidden rounded-full bg-blue-50 px-4 py-2 text-sm font-black text-blue-600 dark:bg-blue-500/15 dark:text-blue-300 sm:block">
                                {{ $materialsCount }} шт.
                            </div>
                        </div>

                        <div class="mt-8 inline-flex items-center rounded-2xl bg-blue-600 px-5 py-4 text-sm font-black text-white shadow-lg shadow-blue-600/20 transition group-hover:bg-blue-700 dark:bg-blue-500 dark:group-hover:bg-blue-400">
                            Перейти к материалам →
                        </div>
                    </a>

                    <a href="{{ route('tests.index', $course) }}"
                       class="group overflow-hidden rounded-[2rem] border border-white bg-white p-7 shadow-sm shadow-slate-200/70 transition hover:-translate-y-1 hover:shadow-xl hover:shadow-violet-100 dark:border-white/10 dark:bg-white/[0.04] dark:shadow-none">
                        <div class="flex items-start justify-between gap-6">
                            <div>
                                <div class="mb-5 flex h-16 w-16 items-center justify-center rounded-3xl bg-violet-50 text-3xl text-violet-600 shadow-inner dark:bg-violet-500/15 dark:text-violet-300">
                                    🧪
                                </div>

                                <h2 class="text-3xl font-black text-slate-950 dark:text-white">
                                    Тесты курса
                                </h2>

                                <p class="mt-3 max-w-md text-sm font-semibold leading-6 text-slate-500 dark:text-slate-400">
                                    Перейти к тестам, прохождению и просмотру результатов.
                                </p>
                            </div>

                            <div class="hidden rounded-full bg-violet-50 px-4 py-2 text-sm font-black text-violet-600 dark:bg-violet-500/15 dark:text-violet-300 sm:block">
                                {{ $testsCount }} шт.
                            </div>
                        </div>

                        <div class="mt-8 inline-flex items-center rounded-2xl bg-green-600 px-5 py-4 text-sm font-black text-white shadow-lg shadow-violet-600/20 transition group-hover:bg-violet-700 dark:bg-violet-500 dark:group-hover:bg-violet-400">
                            Перейти к тестам →
                        </div>
                    </a>
                </section>

                <section class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    @if($isStudent)
                        <div class="rounded-[1.7rem] border border-white bg-white p-6 shadow-sm shadow-slate-200/70 dark:border-white/10 dark:bg-white/[0.04] dark:shadow-none">
                            <h2 class="text-lg font-black text-slate-950 dark:text-white">📊 Прогресс</h2>

                            <p class="mt-4 text-4xl font-black text-slate-950 dark:text-white">{{ $progress }}%</p>

                            <p class="mt-1 text-sm font-semibold text-slate-500 dark:text-slate-400">
                                @if($availableTestsCount > 0)
                                    Пройдено тестов: {{ $completedTestsCount }} из {{ $availableTestsCount }}
                                @else
                                    Тесты пока не добавлены
                                @endif
                            </p>

                            <div class="mt-5 h-3 overflow-hidden rounded-full bg-slate-100 dark:bg-white/5">
                                <div class="h-full rounded-full bg-blue-600 dark:bg-blue-400" style="width: {{ min(100, max(0, $progress)) }}%"></div>
                            </div>
                        </div>
                    @endif

                    @if($canManage)
                        <a href="{{ route('courses.students', $course) }}"
                           class="rounded-[1.7rem] border border-white bg-white p-6 shadow-sm shadow-slate-200/70 transition hover:-translate-y-1 hover:shadow-xl hover:shadow-emerald-100 dark:border-white/10 dark:bg-white/[0.04] dark:shadow-none">
                            <h2 class="text-lg font-black text-slate-950 dark:text-white">👥 Студенты</h2>
                            <p class="mt-4 text-sm font-semibold leading-6 text-slate-500 dark:text-slate-400">
                                Назначить студентов на курс или изменить состав участников.
                            </p>
                        </a>

                        <a href="{{ route('courses.edit', $course) }}"
                           class="rounded-[1.7rem] border border-white bg-white p-6 shadow-sm shadow-slate-200/70 transition hover:-translate-y-1 hover:shadow-xl hover:shadow-orange-100 dark:border-white/10 dark:bg-white/[0.04] dark:shadow-none">
                            <h2 class="text-lg font-black text-slate-950 dark:text-white">✏️ Настройки курса</h2>
                            <p class="mt-4 text-sm font-semibold leading-6 text-slate-500 dark:text-slate-400">
                                Изменить название, описание и обложку курса.
                            </p>
                        </a>
                    @endif
                </section>
            </div>
        </div>
    </div>
</x-app-layout>