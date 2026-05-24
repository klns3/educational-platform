<x-app-layout>
    <div class="min-h-screen bg-[#f6f8fc] text-slate-950 transition-colors duration-300 dark:bg-[#07111f] dark:text-white">
        <div class="relative mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
            <section class="mb-7 grid gap-6 xl:grid-cols-[1fr_380px]">
                <div>
                    <p class="mb-4 inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700 dark:border-blue-400/20 dark:bg-blue-500/10 dark:text-blue-200">
                        {{ $isAdminCurator ? 'Куратор администратора' : 'Куратор преподавателя' }}
                    </p>

                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <h1 class="text-3xl font-black tracking-tight text-slate-950 sm:text-4xl dark:text-white">
                            Цифровой куратор студентов
                        </h1>

                        <button type="button"
                                onclick="window.print()"
                                class="print-hidden inline-flex w-fit items-center justify-center rounded-xl border border-slate-200 bg-white/70 px-4 py-2 text-sm font-black text-slate-700 transition hover:border-blue-400 hover:text-blue-600 dark:border-white/10 dark:bg-white/[0.06] dark:text-slate-200 dark:hover:text-blue-300">
                            Печать
                        </button>
                    </div>

                    <p class="mt-3 max-w-3xl text-sm font-semibold leading-6 text-slate-500 sm:text-base dark:text-slate-300">
                        {{ $isAdminCurator
                            ? 'Выберите любого студента системы, чтобы увидеть его учебный риск, прогресс, слабые темы и рекомендации.'
                            : 'Выберите студента из своих курсов, чтобы увидеть его учебный риск, прогресс, слабые темы и рекомендации.' }}
                    </p>
                </div>

                <form method="GET" action="{{ route('teacher-curator.index') }}" class="glass-panel rounded-[1.2rem] p-5 ring-2 ring-blue-500/20">
                    <div class="mb-4 rounded-2xl bg-blue-600 px-4 py-3 text-center shadow-lg shadow-blue-600/20 dark:bg-blue-500">
                        <p class="text-lg font-black uppercase tracking-wide text-white">
                            Выберите студента
                        </p>
                    </div>

                    <label class="mb-2 block text-sm font-black text-slate-700 dark:text-slate-200">
                        Студент
                    </label>

                    <select name="student_id"
                            class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-950 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-4 focus:ring-blue-500/10 dark:border-white/10 dark:bg-white/[0.04] dark:text-white"
                            onchange="this.form.submit()">
                        @forelse($students as $student)
                            <option value="{{ $student->id }}" @selected($selectedStudent?->id === $student->id)>
                                {{ $student->name }}{{ $student->classGroup ? ' · ' . $student->classGroup->name : '' }}
                            </option>
                        @empty
                            <option>Студентов пока нет</option>
                        @endforelse
                    </select>

                </form>
            </section>

            @if(!$selectedStudent)
                <section class="glass-panel rounded-[1.2rem] p-10 text-center">
                    <h2 class="text-2xl font-black text-slate-950 dark:text-white">Нет студентов для анализа</h2>
                    <p class="mt-2 text-sm font-semibold text-slate-500 dark:text-slate-400">
                        {{ $isAdminCurator
                            ? 'Когда в системе появятся студенты, они будут доступны в списке куратора.'
                            : 'Когда студенты будут записаны на ваши курсы, они появятся в списке куратора.' }}
                    </p>
                </section>
            @else
                @php
                    $metricCards = [
                        ['label' => 'Прогресс', 'value' => $progressPercent . '%', 'hint' => 'Пройдено ' . $completedTestsCount . ' из ' . $availableTestsCount, 'class' => 'text-blue-600 dark:text-blue-300'],
                        ['label' => 'Средний результат', 'value' => $averagePercent . '%', 'hint' => $bestPercent > 0 ? 'Лучший: ' . $bestPercent . '%' : 'Попыток пока нет', 'class' => 'text-emerald-600 dark:text-emerald-300'],
                        ['label' => 'Серия', 'value' => $currentStreak . ' дн.', 'hint' => 'Дни активности подряд', 'class' => 'text-violet-600 dark:text-violet-300'],
                        ['label' => 'Ошибочные попытки', 'value' => $failedAttemptsCount, 'hint' => 'Результаты ниже 70%', 'class' => 'text-orange-600 dark:text-orange-300'],
                    ];
                @endphp

                <section class="mb-6 grid gap-6 xl:grid-cols-[0.9fr_1.1fr]">
                    <div class="glass-panel rounded-[1.2rem] p-6">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-sm font-bold text-slate-500 dark:text-slate-400">Учебный статус</p>
                                <h2 class="mt-2 text-2xl font-black text-slate-950 dark:text-white">{{ $riskProfile['label'] }}</h2>
                            </div>

                            <span class="rounded-full px-3 py-1 text-xs font-black ring-1 {{ $riskProfile['class'] }}">
                                {{ $riskScore }}/100
                            </span>
                        </div>

                        <p class="mt-4 text-sm font-semibold leading-6 text-slate-500 dark:text-slate-400">
                            {{ $riskProfile['description'] }}
                        </p>

                        <div class="mt-5 h-3 overflow-hidden rounded-full bg-slate-100 dark:bg-white/5">
                            <div class="h-full rounded-full {{ $riskProfile['bar'] }}" style="width: {{ min(100, max(0, $riskScore)) }}%"></div>
                        </div>
                    </div>

                    <div class="glass-panel rounded-[1.2rem] p-6">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <p class="text-sm font-bold text-slate-500 dark:text-slate-400">Следующий лучший шаг</p>
                                <h2 class="mt-2 text-2xl font-black text-slate-950 dark:text-white">{{ $nextBestStep['title'] }}</h2>
                                <p class="mt-2 text-sm font-semibold leading-6 text-slate-500 dark:text-slate-400">{{ $nextBestStep['description'] }}</p>
                            </div>

                            <span class="w-fit rounded-full px-3 py-1 text-xs font-black {{ $paceIndicator['class'] }}">
                                {{ $paceIndicator['label'] }}
                            </span>
                        </div>

                        <div class="mt-5 flex flex-wrap gap-3">
                            <a href="{{ route('messages.chat', $selectedStudent) }}"
                               class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-5 py-3 text-sm font-black text-white shadow-lg shadow-blue-600/20 transition hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-400">
                                Обсудить со студентом
                            </a>

                            <a href="{{ route('results.my') }}"
                               class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-5 py-3 text-sm font-black text-slate-700 transition hover:border-blue-400 hover:text-blue-600 dark:border-white/10 dark:text-slate-200">
                                Раздел результатов
                            </a>
                        </div>
                    </div>
                </section>

                <section class="mb-6 grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach($metricCards as $card)
                        <div class="glass-panel rounded-[1.2rem] p-5">
                            <p class="text-sm font-bold text-slate-500 dark:text-slate-400">{{ $card['label'] }}</p>
                            <p class="mt-2 text-3xl font-black {{ $card['class'] }}">{{ $card['value'] }}</p>
                            <p class="mt-1 text-xs font-black text-slate-400 dark:text-slate-500">{{ $card['hint'] }}</p>
                        </div>
                    @endforeach
                </section>

                <section class="mb-6 grid gap-6 xl:grid-cols-[1.15fr_0.85fr]">
                    <div class="glass-panel rounded-[1.2rem] p-6">
                        <div class="mb-5">
                            <h2 class="text-xl font-black text-slate-950 dark:text-white">
                                Цифровой профиль студента
                            </h2>
                            <p class="mt-1 text-sm font-semibold text-slate-500 dark:text-slate-400">
                                Сводная модель для педагогического сопровождения: цель, вовлечённость, темп, компетенции и сигналы поддержки.
                            </p>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                            @foreach($digitalProfile as $item)
                                <div class="rounded-2xl bg-slate-50 p-4 ring-1 ring-slate-200/70 dark:bg-white/[0.04] dark:ring-white/10">
                                    <p class="text-xs font-black uppercase tracking-wide text-slate-400 dark:text-slate-500">
                                        {{ $item['label'] }}
                                    </p>
                                    <p class="mt-2 break-words text-lg font-black text-slate-950 dark:text-white">
                                        {{ $item['value'] }}
                                    </p>
                                    <p class="mt-1 text-xs font-black leading-4 text-slate-400 dark:text-slate-500">
                                        {{ $item['hint'] }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="glass-panel rounded-[1.2rem] p-6">
                        <p class="text-sm font-bold text-slate-500 dark:text-slate-400">
                            Тип учебного поведения
                        </p>

                        <div class="mt-3 rounded-2xl p-4 {{ $behaviorScenario['class'] }}">
                            <p class="text-xl font-black">
                                {{ $behaviorScenario['label'] }}
                            </p>
                            <p class="mt-2 text-sm font-semibold leading-6 opacity-90">
                                {{ $behaviorScenario['description'] }}
                            </p>
                        </div>

                        <div class="mt-5">
                            <div class="flex items-center justify-between text-xs font-black text-slate-500 dark:text-slate-400">
                                <span>Индекс вовлечённости</span>
                                <span>{{ $engagementScore }}/100</span>
                            </div>

                            <div class="mt-2 h-3 overflow-hidden rounded-full bg-slate-100 dark:bg-white/5">
                                <div class="h-full rounded-full bg-violet-600 dark:bg-violet-400"
                                     style="width: {{ min(100, max(0, $engagementScore)) }}%">
                                </div>
                            </div>
                        </div>

                        <div class="mt-5 flex flex-wrap gap-3">
                            <a href="{{ route('messages.chat', $selectedStudent) }}"
                               class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-4 py-2 text-sm font-black text-white transition hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-400">
                                Написать студенту
                            </a>

                            <a href="{{ route('support-tickets.index') }}"
                               class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-4 py-2 text-sm font-black text-slate-700 transition hover:border-blue-400 hover:text-blue-600 dark:border-white/10 dark:text-slate-200">
                                Заявки
                            </a>
                        </div>
                    </div>
                </section>

                <section class="mb-6 grid gap-6 xl:grid-cols-[0.85fr_1.15fr]">
                    <div class="glass-panel rounded-[1.2rem] p-6">
                        <div class="mb-5 flex items-center justify-between gap-4">
                            <div>
                                <h2 class="text-xl font-black text-slate-950 dark:text-white">
                                    Объяснение риска
                                </h2>
                                <p class="mt-1 text-sm font-semibold text-slate-500 dark:text-slate-400">
                                    Факторы, из которых складывается текущий риск студента.
                                </p>
                            </div>

                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-500 dark:bg-white/5 dark:text-slate-400">
                                {{ $riskScore }}/100
                            </span>
                        </div>

                        <div class="grid gap-3">
                            @foreach($riskFactors as $factor)
                                <div class="rounded-2xl p-4 {{ $factor['class'] }}">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <p class="font-black">{{ $factor['label'] }}</p>
                                            <p class="mt-1 text-sm font-semibold leading-5 opacity-90">{{ $factor['description'] }}</p>
                                        </div>

                                        <div class="text-right">
                                            <p class="text-sm font-black">{{ $factor['value'] }}</p>
                                            <p class="mt-1 text-xs font-black opacity-70">+{{ $factor['impact'] }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="glass-panel rounded-[1.2rem] p-6">
                        <div class="mb-5 flex items-center justify-between gap-4">
                            <div>
                                <h2 class="text-xl font-black text-slate-950 dark:text-white">
                                    Карта компетенций
                                </h2>
                                <p class="mt-1 text-sm font-semibold text-slate-500 dark:text-slate-400">
                                    Темы отсортированы от слабых к более освоенным.
                                </p>
                            </div>

                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-500 dark:bg-white/5 dark:text-slate-400">
                                {{ $competencyMap->count() }} тем
                            </span>
                        </div>

                        <div class="grid gap-3">
                            @forelse($competencyMap as $competency)
                                <div class="rounded-2xl bg-slate-50 p-4 ring-1 ring-slate-200/70 dark:bg-white/[0.04] dark:ring-white/10">
                                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                        <div class="min-w-0">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <p class="break-words font-black text-slate-950 dark:text-white">
                                                    {{ $competency['topic'] }}
                                                </p>
                                                <span class="rounded-full px-2.5 py-1 text-xs font-black {{ $competency['class'] }}">
                                                    {{ $competency['status'] }}
                                                </span>
                                            </div>

                                            <p class="mt-1 text-sm font-semibold text-slate-500 dark:text-slate-400">
                                                {{ $competency['course_title'] }} · {{ $competency['correct_count'] }} из {{ $competency['total_count'] }} правильных
                                            </p>
                                        </div>

                                        <p class="shrink-0 text-2xl font-black text-slate-950 dark:text-white">
                                            {{ $competency['mastery_percent'] }}%
                                        </p>
                                    </div>

                                    <div class="mt-4 h-2 overflow-hidden rounded-full bg-white dark:bg-white/5">
                                        <div class="h-full rounded-full {{ $competency['bar'] }}"
                                             style="width: {{ min(100, max(0, $competency['mastery_percent'])) }}%">
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center dark:border-white/10 dark:bg-white/[0.03]">
                                    <p class="font-black text-slate-950 dark:text-white">Карты компетенций пока нет</p>
                                    <p class="mt-2 text-sm font-semibold text-slate-500 dark:text-slate-400">
                                        Она появится после ответов на вопросы с указанными темами.
                                    </p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </section>

                <section class="mb-6 grid gap-6 xl:grid-cols-[1.1fr_0.9fr]">
                    <div class="glass-panel rounded-[1.2rem] p-6">
                        <div class="mb-5">
                            <h2 class="text-xl font-black text-slate-950 dark:text-white">
                                План сопровождения на неделю
                            </h2>
                            <p class="mt-1 text-sm font-semibold text-slate-500 dark:text-slate-400">
                                Что стоит предложить студенту в ближайшие дни.
                            </p>
                        </div>

                        <div class="grid gap-3">
                            @foreach($weeklyPlan as $planItem)
                                <div class="rounded-2xl bg-slate-50 p-4 ring-1 ring-slate-200/70 dark:bg-white/[0.04] dark:ring-white/10">
                                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                        <div class="min-w-0">
                                            <p class="text-xs font-black uppercase tracking-wide text-blue-600 dark:text-blue-300">
                                                {{ $planItem['day'] }}
                                            </p>
                                            <p class="mt-1 break-words font-black text-slate-950 dark:text-white">
                                                {{ $planItem['title'] }}
                                            </p>
                                            <p class="mt-1 text-sm font-semibold leading-5 text-slate-500 dark:text-slate-400">
                                                {{ $planItem['description'] }}
                                            </p>
                                        </div>

                                        <a href="{{ $planItem['route'] }}"
                                           class="shrink-0 rounded-xl border border-slate-200 px-4 py-2 text-sm font-black text-slate-700 transition hover:border-blue-400 hover:text-blue-600 dark:border-white/10 dark:text-slate-200 dark:hover:text-blue-300">
                                            {{ $planItem['label'] }}
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="grid gap-6">
                        <div class="glass-panel rounded-[1.2rem] p-6">
                            <h2 class="text-xl font-black text-slate-950 dark:text-white">
                                Сигналы вмешательства
                            </h2>
                            <p class="mt-1 text-sm font-semibold text-slate-500 dark:text-slate-400">
                                Основания для консультации, сообщения или разбора ошибок.
                            </p>

                            <div class="mt-5 grid gap-3">
                                @foreach($interventionSignals as $signal)
                                    <div class="rounded-2xl p-4 {{ $signal['class'] }}">
                                        <p class="text-xs font-black uppercase tracking-wide opacity-70">{{ $signal['level'] }}</p>
                                        <p class="mt-1 font-black">{{ $signal['title'] }}</p>
                                        <p class="mt-1 text-sm font-semibold leading-5 opacity-90">{{ $signal['description'] }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="glass-panel rounded-[1.2rem] p-6">
                            <h2 class="text-xl font-black text-slate-950 dark:text-white">
                                Сценарный прогноз
                            </h2>
                            <p class="mt-1 text-sm font-semibold text-slate-500 dark:text-slate-400">
                                Как может измениться риск при разных действиях.
                            </p>

                            <div class="mt-5 grid gap-3">
                                @foreach($riskForecast as $forecast)
                                    <div class="rounded-2xl bg-slate-50 p-4 ring-1 ring-slate-200/70 dark:bg-white/[0.04] dark:ring-white/10">
                                        <div class="flex items-start justify-between gap-4">
                                            <div>
                                                <p class="font-black text-slate-950 dark:text-white">{{ $forecast['label'] }}</p>
                                                <p class="mt-1 text-sm font-semibold leading-5 text-slate-500 dark:text-slate-400">{{ $forecast['hint'] }}</p>
                                            </div>

                                            <p class="shrink-0 text-2xl font-black {{ $forecast['class'] }}">
                                                {{ $forecast['value'] }}
                                            </p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </section>

                <section class="mb-6 grid gap-6 xl:grid-cols-2">
                    <div class="glass-panel rounded-[1.2rem] p-6">
                        <h2 class="text-xl font-black text-slate-950 dark:text-white">Слабые зоны</h2>
                        <p class="mt-1 text-sm font-semibold text-slate-500 dark:text-slate-400">Темы, где студент чаще ошибается.</p>

                        <div class="mt-5 grid gap-3">
                            @forelse($weakZones as $zone)
                                <div class="rounded-2xl bg-slate-50 p-4 ring-1 ring-slate-200/70 dark:bg-white/[0.04] dark:ring-white/10">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <p class="break-words font-black text-slate-950 dark:text-white">{{ $zone['topic'] }}</p>
                                            <p class="mt-1 text-sm font-semibold text-slate-500 dark:text-slate-400">{{ $zone['course_title'] }} · {{ $zone['test_title'] }}</p>
                                        </div>

                                        <span class="shrink-0 rounded-full bg-orange-50 px-2.5 py-1 text-xs font-black text-orange-600 dark:bg-orange-500/15 dark:text-orange-300">
                                            {{ $zone['wrong_count'] }} ошибок
                                        </span>
                                    </div>
                                </div>
                            @empty
                                <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center dark:border-white/10 dark:bg-white/[0.03]">
                                    <p class="font-black text-slate-950 dark:text-white">Слабых зон пока нет</p>
                                    <p class="mt-2 text-sm font-semibold text-slate-500 dark:text-slate-400">Ошибки по вопросам появятся здесь после прохождения тестов.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <div class="glass-panel rounded-[1.2rem] p-6">
                        <h2 class="text-xl font-black text-slate-950 dark:text-white">Приоритетные тесты</h2>
                        <p class="mt-1 text-sm font-semibold text-slate-500 dark:text-slate-400">Что студенту стоит закрыть или улучшить.</p>

                        <div class="mt-5 divide-y divide-slate-100 dark:divide-white/10">
                            @forelse($recommendedTests as $item)
                                <div class="flex flex-col gap-3 py-4 first:pt-0 last:pb-0 sm:flex-row sm:items-center sm:justify-between">
                                    <div class="min-w-0">
                                        <p class="truncate font-black text-slate-950 dark:text-white">{{ $item['test']->title }}</p>
                                        <p class="mt-1 truncate text-sm font-semibold text-slate-500 dark:text-slate-400">{{ $item['test']->course?->title ?? 'Без курса' }} · {{ $item['hint'] }}</p>
                                    </div>

                                    <a href="{{ route('tests.show', $item['test']) }}"
                                       class="shrink-0 rounded-xl border border-slate-200 px-4 py-2 text-sm font-black text-slate-700 transition hover:border-blue-400 hover:text-blue-600 dark:border-white/10 dark:text-slate-200">
                                        Открыть
                                    </a>
                                </div>
                            @empty
                                <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center dark:border-white/10 dark:bg-white/[0.03]">
                                    <p class="font-black text-slate-950 dark:text-white">Нет доступных тестов</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </section>

                <section class="grid gap-6 xl:grid-cols-[0.9fr_1.1fr]">
                    <div class="glass-panel rounded-[1.2rem] p-6">
                        <h2 class="text-xl font-black text-slate-950 dark:text-white">История улучшений</h2>

                        <div class="mt-5 grid gap-3 sm:grid-cols-2">
                            @foreach($improvementHistory as $item)
                                <div class="rounded-2xl bg-slate-50 p-4 ring-1 ring-slate-200/70 dark:bg-white/[0.04] dark:ring-white/10">
                                    <p class="text-sm font-bold text-slate-500 dark:text-slate-400">{{ $item['label'] }}</p>
                                    <p class="mt-2 text-2xl font-black text-slate-950 dark:text-white">{{ $item['value'] }}</p>
                                    <p class="mt-1 text-xs font-black leading-4 text-slate-400 dark:text-slate-500">{{ $item['hint'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="glass-panel rounded-[1.2rem] p-6">
                        <h2 class="text-xl font-black text-slate-950 dark:text-white">Прогресс по курсам</h2>

                        <div class="mt-5 grid gap-3">
                            @forelse($courseProgress as $item)
                                <div class="rounded-2xl bg-slate-50 p-4 ring-1 ring-slate-200/70 dark:bg-white/[0.04] dark:ring-white/10">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <p class="truncate font-black text-slate-950 dark:text-white">{{ $item['course']->title }}</p>
                                            <p class="mt-1 text-sm font-semibold text-slate-500 dark:text-slate-400">
                                                {{ $item['completed_count'] }} из {{ $item['tests_count'] }} тестов · средний результат {{ $item['average_percent'] }}%
                                            </p>
                                        </div>

                                        <span class="shrink-0 rounded-full bg-blue-50 px-2.5 py-1 text-xs font-black text-blue-600 dark:bg-blue-500/15 dark:text-blue-300">
                                            {{ $item['progress_percent'] }}%
                                        </span>
                                    </div>

                                    <div class="mt-4 h-2 overflow-hidden rounded-full bg-white dark:bg-white/5">
                                        <div class="h-full rounded-full bg-blue-600 dark:bg-blue-400" style="width: {{ min(100, max(0, $item['progress_percent'])) }}%"></div>
                                    </div>
                                </div>
                            @empty
                                <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center dark:border-white/10 dark:bg-white/[0.03]">
                                    <p class="font-black text-slate-950 dark:text-white">Нет курсов для этого студента</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </section>
            @endif
        </div>
    </div>
</x-app-layout>
