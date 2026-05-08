<x-app-layout>
    @php
        $user = auth()->user();

        $isStudent = $user->role === 'student';
        $canManage = in_array($user->role, ['admin', 'teacher']);

        $accentClasses = [
            [
                'icon' => 'bg-blue-50 text-blue-600 dark:bg-blue-500/15 dark:text-blue-300',
                'shadow' => 'hover:shadow-blue-100',
                'bar' => 'bg-blue-600 dark:bg-blue-400',
            ],
            [
                'icon' => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-300',
                'shadow' => 'hover:shadow-emerald-100',
                'bar' => 'bg-emerald-600 dark:bg-emerald-400',
            ],
            [
                'icon' => 'bg-violet-50 text-violet-600 dark:bg-violet-500/15 dark:text-violet-300',
                'shadow' => 'hover:shadow-violet-100',
                'bar' => 'bg-violet-600 dark:bg-violet-400',
            ],
            [
                'icon' => 'bg-orange-50 text-orange-600 dark:bg-orange-500/15 dark:text-orange-300',
                'shadow' => 'hover:shadow-orange-100',
                'bar' => 'bg-orange-500 dark:bg-orange-400',
            ],
        ];

        $totalCourses = $courses->count();

        $totalStudents = $courses
            ->filter(fn ($course) => isset($course->students_count))
            ->sum(fn ($course) => (int) $course->students_count);

        $totalAvailableTests = $courses
            ->filter(fn ($course) => isset($course->available_tests_count))
            ->sum(fn ($course) => (int) $course->available_tests_count);

        $averageProgress = $isStudent && $totalCourses > 0
            ? round($courses->avg(fn ($course) => (int) ($course->progress_percent ?? 0)))
            : 0;
    @endphp

    <div class="min-h-screen bg-[#f6f8fc] text-slate-950 transition-colors duration-300 dark:bg-[#07111f] dark:text-white">
        <div class="relative overflow-hidden">
            <div class="pointer-events-none absolute inset-0 hidden dark:block">
                <div class="absolute left-[18%] top-0 h-80 w-80 rounded-full bg-blue-600/20 blur-3xl"></div>
                <div class="absolute right-0 top-10 h-96 w-96 rounded-full bg-violet-600/20 blur-3xl"></div>
                <div class="absolute bottom-0 left-1/2 h-72 w-72 rounded-full bg-cyan-500/10 blur-3xl"></div>
            </div>

            <div class="relative mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
                <section class="mb-7 grid gap-6 lg:grid-cols-[1fr_330px]">
                    <div>
                        <p class="mb-4 inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700 dark:border-blue-400/20 dark:bg-blue-500/10 dark:text-blue-200">
                            Курсы
                        </p>

                        <h1 class="text-3xl font-black tracking-tight text-slate-950 sm:text-4xl dark:text-white">
                            @if($isStudent)
                                Доступные курсы
                            @else
                                Управление курсами
                            @endif
                        </h1>

                        <p class="mt-3 max-w-2xl text-sm font-semibold text-slate-500 sm:text-base dark:text-slate-300">
                            @if($isStudent)
                                Здесь собраны курсы, материалы и тесты, которые доступны для обучения.
                            @else
                                Создавайте курсы, назначайте студентов, добавляйте материалы и тесты. Всё по делу, без канцелярской пыли.
                            @endif
                        </p>
                    </div>

                    <div class="rounded-[1.7rem] border border-white bg-white p-5 shadow-sm shadow-slate-200/70 dark:border-white/10 dark:bg-white/[0.04] dark:shadow-none">
                        <p class="text-sm font-bold text-slate-500 dark:text-slate-400">
                            @if($isStudent)
                                Средний прогресс
                            @else
                                Всего курсов
                            @endif
                        </p>

                        <div class="mt-3 flex items-end justify-between gap-4">
                            <div>
                                <p class="text-4xl font-black text-slate-950 dark:text-white">
                                    @if($isStudent)
                                        {{ $averageProgress }}%
                                    @else
                                        {{ $totalCourses }}
                                    @endif
                                </p>

                                <p class="mt-1 text-xs font-black text-blue-600 dark:text-blue-300">
                                    @if($isStudent)
                                        По доступным курсам
                                    @else
                                        В системе
                                    @endif
                                </p>
                            </div>

                            <div class="rounded-2xl bg-blue-50 px-4 py-3 text-2xl dark:bg-blue-500/15">
                                📚
                            </div>
                        </div>

                        @if($isStudent)
                            <div class="mt-5 h-3 overflow-hidden rounded-full bg-slate-100 dark:bg-white/5">
                                <div class="h-full rounded-full bg-blue-600 dark:bg-blue-400" style="width: {{ min(100, max(0, $averageProgress)) }}%"></div>
                            </div>
                        @else
                            @if($canManage)
                                <a href="{{ route('courses.create') }}"
                                   class="mt-5 inline-flex w-full items-center justify-center rounded-2xl bg-blue-600 px-5 py-4 text-sm font-black text-white shadow-lg shadow-blue-600/20 transition hover:-translate-y-0.5 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-400">
                                    Создать курс
                                </a>
                            @endif
                        @endif
                    </div>
                </section>

                @if(session('success'))
                    <div class="mb-6 rounded-[1.4rem] border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-black text-emerald-700 shadow-sm shadow-emerald-100 dark:border-emerald-400/20 dark:bg-emerald-500/10 dark:text-emerald-300 dark:shadow-none">
                        {{ session('success') }}
                    </div>
                @endif

                <section class="mb-6 grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
                    <div class="rounded-[1.7rem] border border-white bg-white p-5 shadow-sm shadow-slate-200/70 transition hover:-translate-y-1 hover:shadow-xl hover:shadow-blue-100 dark:border-white/10 dark:bg-white/[0.04] dark:shadow-none">
                        <div class="flex items-center gap-4">
                            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-50 text-2xl text-blue-600 shadow-inner dark:bg-blue-500/15 dark:text-blue-300">
                                📚
                            </div>
                            <div>
                                <p class="text-sm font-bold text-slate-500 dark:text-slate-400">Курсов</p>
                                <p class="mt-1 text-4xl font-black text-slate-950 dark:text-white">{{ $totalCourses }}</p>
                                <p class="mt-1 text-xs font-black text-blue-600 dark:text-blue-300">
                                    @if($isStudent)
                                        Доступно вам
                                    @else
                                        Создано в системе
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-[1.7rem] border border-white bg-white p-5 shadow-sm shadow-slate-200/70 transition hover:-translate-y-1 hover:shadow-xl hover:shadow-emerald-100 dark:border-white/10 dark:bg-white/[0.04] dark:shadow-none">
                        <div class="flex items-center gap-4">
                            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-50 text-2xl text-emerald-600 shadow-inner dark:bg-emerald-500/15 dark:text-emerald-300">
                                @if($isStudent)
                                    🧪
                                @else
                                    👥
                                @endif
                            </div>
                            <div>
                                <p class="text-sm font-bold text-slate-500 dark:text-slate-400">
                                    @if($isStudent)
                                        Доступных тестов
                                    @else
                                        Студентов
                                    @endif
                                </p>
                                <p class="mt-1 text-4xl font-black text-slate-950 dark:text-white">
                                    @if($isStudent)
                                        {{ $totalAvailableTests }}
                                    @else
                                        {{ $totalStudents }}
                                    @endif
                                </p>
                                <p class="mt-1 text-xs font-black text-emerald-600 dark:text-emerald-300">
                                    @if($isStudent)
                                        В ваших курсах
                                    @else
                                        В курсах
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-[1.7rem] border border-white bg-white p-5 shadow-sm shadow-slate-200/70 transition hover:-translate-y-1 hover:shadow-xl hover:shadow-violet-100 dark:border-white/10 dark:bg-white/[0.04] dark:shadow-none">
                        <div class="flex items-center gap-4">
                            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-violet-50 text-2xl text-violet-600 shadow-inner dark:bg-violet-500/15 dark:text-violet-300">
                                👨‍🏫
                            </div>
                            <div>
                                <p class="text-sm font-bold text-slate-500 dark:text-slate-400">Роль</p>
                                <p class="mt-1 text-2xl font-black text-slate-950 dark:text-white">
                                    @if($user->role === 'admin')
                                        Админ
                                    @elseif($user->role === 'teacher')
                                        Преподаватель
                                    @else
                                        Студент
                                    @endif
                                </p>
                                <p class="mt-1 text-xs font-black text-violet-600 dark:text-violet-300">Текущий доступ</p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-[1.7rem] border border-white bg-white p-5 shadow-sm shadow-slate-200/70 transition hover:-translate-y-1 hover:shadow-xl hover:shadow-orange-100 dark:border-white/10 dark:bg-white/[0.04] dark:shadow-none">
                        <div class="flex items-center gap-4">
                            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-orange-50 text-2xl text-orange-600 shadow-inner dark:bg-orange-500/15 dark:text-orange-300">
                                ⚡
                            </div>
                            <div>
                                <p class="text-sm font-bold text-slate-500 dark:text-slate-400">Действие</p>
                                <p class="mt-1 text-2xl font-black text-slate-950 dark:text-white">
                                    @if($canManage)
                                        Создать
                                    @else
                                        Учиться
                                    @endif
                                </p>
                                <p class="mt-1 text-xs font-black text-orange-600 dark:text-orange-300">
                                    @if($canManage)
                                        Курс или материал
                                    @else
                                        Открыть курс
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </section>

                @if($courses->isNotEmpty())
                    <section class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
                        @foreach($courses as $course)
                            @php
                                $accent = $accentClasses[$loop->index % count($accentClasses)];
                                $progress = (int) ($course->progress_percent ?? 0);
                                $availableTestsCount = (int) ($course->available_tests_count ?? 0);
                                $completedTestsCount = (int) ($course->completed_tests_count ?? 0);
                            @endphp

                            <article class="group overflow-hidden rounded-[1.7rem] border border-white bg-white shadow-sm shadow-slate-200/70 transition hover:-translate-y-1 hover:shadow-xl {{ $accent['shadow'] }} dark:border-white/10 dark:bg-white/[0.04] dark:shadow-none">
                                <div class="relative">
                                    @if($course->cover_url)
                                        <img
                                            src="{{ $course->cover_url }}"
                                            alt="{{ $course->title }}"
                                            class="h-48 w-full object-cover"
                                        >
                                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/70 via-slate-950/10 to-transparent"></div>
                                    @else
                                        <div class="flex h-48 w-full items-center justify-center bg-gradient-to-br from-blue-600 via-violet-600 to-cyan-500 px-8 text-center">
                                            <span class="text-3xl font-black leading-tight tracking-tight text-white/90">
                                                {{ mb_substr($course->title, 0, 42) }}
                                            </span>
                                        </div>
                                    @endif

                                    <div class="absolute left-4 top-4 rounded-2xl bg-white/85 px-3 py-2 text-xs font-black text-slate-700 shadow-sm backdrop-blur-xl dark:bg-slate-950/50 dark:text-white">
                                        {{ $course->teacher?->name ?? 'Преподаватель не указан' }}
                                    </div>
                                </div>

                                <div class="p-5">
                                    <div class="flex items-start justify-between gap-4">
                                        <div class="min-w-0">
                                            <h2 class="truncate text-xl font-black text-slate-950 dark:text-white">
                                                {{ $course->title }}
                                            </h2>

                                            <p class="mt-2 line-clamp-3 text-sm font-semibold leading-6 text-slate-500 dark:text-slate-400">
                                                {{ $course->description ?: 'Описание курса пока не добавлено.' }}
                                            </p>
                                        </div>

                                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl text-xl font-black {{ $accent['icon'] }}">
                                            📘
                                        </div>
                                    </div>

                                    @if($isStudent)
                                        <div class="mt-5 rounded-2xl bg-slate-50 p-4 dark:bg-white/[0.04]">
                                            <div class="mb-2 flex items-center justify-between gap-4">
                                                <span class="text-sm font-black text-slate-700 dark:text-slate-200">
                                                    Прогресс курса
                                                </span>
                                                <span class="text-sm font-black text-blue-600 dark:text-blue-300">
                                                    {{ $progress }}%
                                                </span>
                                            </div>

                                            <div class="h-3 overflow-hidden rounded-full bg-slate-100 dark:bg-white/5">
                                                <div class="h-full rounded-full {{ $accent['bar'] }}" style="width: {{ min(100, max(0, $progress)) }}%"></div>
                                            </div>

                                            <p class="mt-3 text-xs font-bold text-slate-500 dark:text-slate-400">
                                                @if($availableTestsCount > 0)
                                                    Пройдено тестов: {{ $completedTestsCount }} из {{ $availableTestsCount }}
                                                @else
                                                    Тесты пока не добавлены
                                                @endif
                                            </p>
                                        </div>
                                    @else
                                        <div class="mt-5 grid grid-cols-2 gap-3">
                                            <div class="rounded-2xl bg-slate-50 p-4 dark:bg-white/[0.04]">
                                                <p class="text-xs font-bold text-slate-500 dark:text-slate-400">Студентов</p>
                                                <p class="mt-1 text-2xl font-black text-slate-950 dark:text-white">
                                                    {{ $course->students_count ?? 0 }}
                                                </p>
                                            </div>

                                            <div class="rounded-2xl bg-slate-50 p-4 dark:bg-white/[0.04]">
                                                <p class="text-xs font-bold text-slate-500 dark:text-slate-400">Преподаватель</p>
                                                <p class="mt-1 truncate text-sm font-black text-slate-950 dark:text-white">
                                                    {{ $course->teacher?->name ?? 'Не указан' }}
                                                </p>
                                            </div>
                                        </div>
                                    @endif

                                    <div class="mt-5 flex flex-wrap gap-2">
                                        <a href="{{ route('courses.show', $course) }}"
                                           class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-4 py-3 text-sm font-black text-white shadow-lg shadow-blue-600/20 transition hover:-translate-y-0.5 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-400">
                                            Открыть
                                        </a>

                                        @if($canManage)
                                            <a href="{{ route('courses.students', $course) }}"
                                               class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-4 py-3 text-sm font-black text-slate-700 transition hover:border-blue-400 hover:text-blue-600 dark:border-white/10 dark:text-slate-200">
                                                Студенты
                                            </a>
                                        @endif

                                        @if($user->role === 'admin' || $user->id === $course->teacher_id)
                                            <a href="{{ route('courses.edit', $course) }}"
                                               class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-4 py-3 text-sm font-black text-slate-700 transition hover:border-amber-400 hover:text-amber-600 dark:border-white/10 dark:text-slate-200 dark:hover:text-amber-300">
                                                Изменить
                                            </a>

                                            <form action="{{ route('courses.destroy', $course) }}"
                                                  method="POST"
                                                  onsubmit="return confirm('Удалить курс?')">
                                                @csrf
                                                @method('DELETE')

                                                <button
                                                    type="submit"
                                                    class="inline-flex items-center justify-center rounded-xl border border-rose-200 px-4 py-3 text-sm font-black text-rose-600 transition hover:bg-rose-50 dark:border-rose-400/20 dark:text-rose-300 dark:hover:bg-rose-500/10">
                                                    Удалить
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </section>
                @else
                    <section class="rounded-[1.7rem] border border-white bg-white p-10 text-center shadow-sm shadow-slate-200/70 dark:border-white/10 dark:bg-white/[0.04] dark:shadow-none">
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-blue-50 text-3xl text-blue-600 dark:bg-blue-500/15 dark:text-blue-300">
                            📚
                        </div>

                        <h2 class="mt-5 text-2xl font-black text-slate-950 dark:text-white">
                            Курсы пока не созданы
                        </h2>

                        <p class="mx-auto mt-2 max-w-md text-sm font-semibold text-slate-500 dark:text-slate-400">
                            @if($isStudent)
                                Вам пока не назначили курсы. Когда преподаватель или администратор добавит вас в курс, он появится здесь.
                            @else
                                Создайте первый курс, добавьте студентов, материалы и тесты. Старый добрый порядок начинается с первой записи.
                            @endif
                        </p>

                        @if($canManage)
                            <a href="{{ route('courses.create') }}"
                               class="mt-6 inline-flex items-center justify-center rounded-2xl bg-blue-600 px-5 py-4 text-sm font-black text-white shadow-lg shadow-blue-600/20 transition hover:-translate-y-0.5 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-400">
                                Создать курс
                            </a>
                        @endif
                    </section>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>