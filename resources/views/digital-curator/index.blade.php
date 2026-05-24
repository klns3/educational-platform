<x-app-layout>
    @php
        $metricCards = [
            [
                'label' => 'Прогресс',
                'value' => $progressPercent . '%',
                'hint' => 'Пройдено ' . $completedTestsCount . ' из ' . $availableTestsCount,
                'icon' => '↗',
                'class' => 'text-blue-600 dark:text-blue-300',
                'box' => 'bg-blue-50 text-blue-600 dark:bg-blue-500/15 dark:text-blue-300',
            ],
            [
                'label' => 'Средний результат',
                'value' => $averagePercent . '%',
                'hint' => $bestPercent > 0 ? 'Лучший результат: ' . $bestPercent . '%' : 'Попыток пока нет',
                'icon' => '✓',
                'class' => 'text-emerald-600 dark:text-emerald-300',
                'box' => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-300',
            ],
            [
                'label' => 'Серия',
                'value' => $currentStreak . ' дн.',
                'hint' => 'Дни активности подряд',
                'icon' => '◆',
                'class' => 'text-violet-600 dark:text-violet-300',
                'box' => 'bg-violet-50 text-violet-600 dark:bg-violet-500/15 dark:text-violet-300',
            ],
            [
                'label' => 'Неудачные попытки',
                'value' => $failedAttemptsCount,
                'hint' => 'Результаты ниже 70%',
                'icon' => '!',
                'class' => 'text-orange-600 dark:text-orange-300',
                'box' => 'bg-orange-50 text-orange-600 dark:bg-orange-500/15 dark:text-orange-300',
            ],
        ];
    @endphp

    <div class="min-h-screen bg-[#f6f8fc] text-slate-950 transition-colors duration-300 dark:bg-[#07111f] dark:text-white">
        <div class="relative mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 lg:py-10">

            <section class="mb-7 grid gap-6 lg:grid-cols-[1fr_380px]">
                <div>
                    <p class="mb-4 inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700 dark:border-blue-400/20 dark:bg-blue-500/10 dark:text-blue-200">
                        Персональная учебная траектория
                    </p>

                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <h1 class="text-3xl font-black tracking-tight text-slate-950 sm:text-4xl dark:text-white">
                            Цифровой куратор
                        </h1>

                        <button type="button"
                                onclick="window.print()"
                                class="print-hidden inline-flex w-fit items-center justify-center rounded-xl border border-slate-200 bg-white/70 px-4 py-2 text-sm font-black text-slate-700 transition hover:border-blue-400 hover:text-blue-600 dark:border-white/10 dark:bg-white/[0.06] dark:text-slate-200 dark:hover:text-blue-300">
                            Печать
                        </button>
                    </div>

                    <p class="mt-3 max-w-3xl text-sm font-semibold leading-6 text-slate-500 sm:text-base dark:text-slate-300">
                        Персональный план на основе твоих курсов, тестов, активности и ближайшего расписания.
                    </p>
                </div>

                <div class="glass-panel rounded-[1.2rem] p-5">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-bold text-slate-500 dark:text-slate-400">
                                Учебный статус
                            </p>
                            <p class="mt-2 text-2xl font-black text-slate-950 dark:text-white">
                                {{ $riskProfile['label'] }}
                            </p>
                        </div>

                        <span class="rounded-full px-3 py-1 text-xs font-black ring-1 {{ $riskProfile['class'] }}">
                            {{ $riskScore }}/100
                        </span>
                    </div>

                    <p class="mt-4 text-sm font-semibold leading-6 text-slate-500 dark:text-slate-400">
                        {{ $riskProfile['description'] }}
                    </p>

                    <div class="mt-5 h-3 overflow-hidden rounded-full bg-slate-100 dark:bg-white/5">
                        <div class="h-full rounded-full {{ $riskProfile['bar'] }}"
                             style="width: {{ min(100, max(0, $riskScore)) }}%">
                        </div>
                    </div>
                </div>
            </section>

            <section class="mb-6 grid gap-6 xl:grid-cols-[0.85fr_1.15fr]">
                <div class="glass-panel rounded-[1.2rem] p-6">
                    <h2 class="text-xl font-black text-slate-950 dark:text-white">
                        Цель обучения
                    </h2>

                    <p class="mt-1 text-sm font-semibold text-slate-500 dark:text-slate-400">
                        Выбранная цель меняет приоритет тестов, материалов и следующего шага.
                    </p>

                    <form method="POST" action="{{ route('digital-curator.goal.update') }}" class="mt-5 grid gap-3">
                        @csrf

                        @foreach($learningGoalOptions as $value => $option)
                            <label class="cursor-pointer rounded-2xl bg-slate-50 p-4 ring-1 ring-slate-200/70 transition hover:bg-white dark:bg-white/[0.04] dark:ring-white/10 dark:hover:bg-white/[0.06]">
                                <div class="flex items-start gap-3">
                                    <input type="radio"
                                           name="learning_goal"
                                           value="{{ $value }}"
                                           @checked($learningGoal === $value)
                                           class="mt-1 border-slate-300 text-blue-600 focus:ring-blue-500">

                                    <span class="min-w-0">
                                        <span class="block font-black text-slate-950 dark:text-white">{{ $option['label'] }}</span>
                                        <span class="mt-1 block text-sm font-semibold leading-5 text-slate-500 dark:text-slate-400">{{ $option['description'] }}</span>
                                    </span>
                                </div>
                            </label>
                        @endforeach

                        <button type="submit"
                                class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-5 py-3 text-sm font-black text-white shadow-lg shadow-blue-600/20 transition hover:-translate-y-0.5 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-400">
                            Сохранить цель
                        </button>
                    </form>
                </div>

                <div class="glass-panel rounded-[1.2rem] p-6">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div>
                            <p class="text-sm font-bold text-slate-500 dark:text-slate-400">
                                Следующий лучший шаг
                            </p>
                            <h2 class="mt-2 text-2xl font-black text-slate-950 dark:text-white">
                                {{ $nextBestStep['title'] }}
                            </h2>
                            <p class="mt-2 max-w-2xl text-sm font-semibold leading-6 text-slate-500 dark:text-slate-400">
                                {{ $nextBestStep['description'] }}
                            </p>
                        </div>

                        <span class="w-fit rounded-full px-3 py-1 text-xs font-black {{ $paceIndicator['class'] }}">
                            {{ $paceIndicator['label'] }}
                        </span>
                    </div>

                    <div class="mt-5 grid gap-4 md:grid-cols-[1fr_auto] md:items-end">
                        <div>
                            <div class="flex items-center justify-between gap-4 text-xs font-black text-slate-500 dark:text-slate-400">
                                <span>Фактический прогресс: {{ $progressPercent }}%</span>
                                <span>Ожидаемый: {{ $paceIndicator['expected_percent'] }}%</span>
                            </div>

                            <div class="mt-2 h-3 overflow-hidden rounded-full bg-slate-100 dark:bg-white/5">
                                <div class="h-full rounded-full bg-blue-600 dark:bg-blue-400"
                                     style="width: {{ min(100, max(0, $progressPercent)) }}%">
                                </div>
                            </div>

                            <p class="mt-3 text-sm font-semibold leading-5 text-slate-500 dark:text-slate-400">
                                {{ $paceIndicator['description'] }}
                            </p>
                        </div>

                        <a href="{{ $nextBestStep['route'] }}"
                           class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-5 py-3 text-sm font-black text-white shadow-lg shadow-blue-600/20 transition hover:-translate-y-0.5 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-400">
                            {{ $nextBestStep['label'] }}
                        </a>
                    </div>

                    @if($riskScore >= 35)
                        <form method="POST" action="{{ route('digital-curator.help-request') }}" class="mt-5 rounded-2xl bg-red-50 p-4 ring-1 ring-red-100 dark:bg-red-500/10 dark:ring-red-400/20">
                            @csrf
                            <input type="hidden"
                                   name="message"
                                   value="Цифровой куратор обнаружил риск отставания. Нужна консультация преподавателя: текущий риск {{ $riskScore }}/100, цель обучения - {{ $learningGoalOptions[$learningGoal]['label'] }}.">

                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <p class="font-black text-red-700 dark:text-red-300">Нужна помощь преподавателя?</p>
                                    <p class="mt-1 text-sm font-semibold text-red-600/80 dark:text-red-200/80">Куратор может создать заявку с причиной обращения.</p>
                                </div>

                                <button type="submit"
                                        class="inline-flex items-center justify-center rounded-xl bg-red-600 px-4 py-2 text-sm font-black text-white transition hover:bg-red-700">
                                    Попросить помощь
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </section>

            <section class="mb-6 grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
                @foreach($metricCards as $card)
                    <div class="glass-panel rounded-[1.2rem] p-5 transition hover:-translate-y-1 hover:shadow-xl hover:shadow-blue-100 dark:hover:shadow-none">
                        <div class="flex items-center gap-4">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl text-lg font-black shadow-inner {{ $card['box'] }}">
                                {{ $card['icon'] }}
                            </div>

                            <div class="min-w-0">
                                <p class="break-words text-sm font-bold text-slate-500 dark:text-slate-400">
                                    {{ $card['label'] }}
                                </p>
                                <p class="mt-1 text-3xl font-black {{ $card['class'] }}">
                                    {{ $card['value'] }}
                                </p>
                                <p class="mt-1 break-words text-xs font-black text-slate-400 dark:text-slate-500">
                                    {{ $card['hint'] }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </section>

            <section class="mb-6 grid gap-6 xl:grid-cols-[1.15fr_0.85fr]">
                <div class="glass-panel rounded-[1.2rem] p-6">
                    <div class="mb-5">
                        <h2 class="text-xl font-black text-slate-950 dark:text-white">
                            Цифровой профиль
                        </h2>
                        <p class="mt-1 text-sm font-semibold text-slate-500 dark:text-slate-400">
                            Сводная модель студента: цель, вовлечённость, темп, освоение тем и сигналы поддержки.
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
                                Факторы, из которых складывается текущий риск.
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
                                Освоение тем по фактическим ответам в тестах.
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
                                    После ответов на вопросы с темами куратор построит профиль освоения.
                                </p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </section>

            <section class="mb-6 grid gap-6 xl:grid-cols-[1.1fr_0.9fr]">
                <div class="glass-panel rounded-[1.2rem] p-6">
                    <div class="mb-5 flex items-center justify-between gap-4">
                        <div>
                            <h2 class="text-xl font-black text-slate-950 dark:text-white">
                                Индивидуальный план на неделю
                            </h2>
                            <p class="mt-1 text-sm font-semibold text-slate-500 dark:text-slate-400">
                                Короткая траектория из действий, материалов и слабых тем.
                            </p>
                        </div>
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
                            Когда куратор считает, что нужна помощь или внимание.
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
                            Что изменит риск
                        </h2>
                        <p class="mt-1 text-sm font-semibold text-slate-500 dark:text-slate-400">
                            Сценарная оценка ближайших действий.
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
                    <div class="mb-5 flex items-center justify-between gap-4">
                        <div>
                            <h2 class="text-xl font-black text-slate-950 dark:text-white">
                                Слабые зоны
                            </h2>
                            <p class="mt-1 text-sm font-semibold text-slate-500 dark:text-slate-400">
                                Темы, где чаще всего были ошибки в ответах.
                            </p>
                        </div>

                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-500 dark:bg-white/5 dark:text-slate-400">
                            {{ $weakZones->count() }} тем
                        </span>
                    </div>

                    <div class="grid gap-3">
                        @forelse($weakZones as $zone)
                            <div class="rounded-2xl bg-slate-50 p-4 ring-1 ring-slate-200/70 dark:bg-white/[0.04] dark:ring-white/10">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="break-words font-black text-slate-950 dark:text-white">{{ $zone['topic'] }}</p>
                                        <p class="mt-1 text-sm font-semibold text-slate-500 dark:text-slate-400">
                                            {{ $zone['course_title'] }} · {{ $zone['test_title'] }}
                                        </p>
                                    </div>

                                    <span class="shrink-0 rounded-full bg-orange-50 px-2.5 py-1 text-xs font-black text-orange-600 dark:bg-orange-500/15 dark:text-orange-300">
                                        {{ $zone['wrong_count'] }} ошибок
                                    </span>
                                </div>
                            </div>
                        @empty
                            <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center dark:border-white/10 dark:bg-white/[0.03]">
                                <p class="font-black text-slate-950 dark:text-white">Слабых зон пока нет</p>
                                <p class="mt-2 text-sm font-semibold text-slate-500 dark:text-slate-400">
                                    Когда появятся ответы с ошибками, куратор покажет проблемные темы.
                                </p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="glass-panel rounded-[1.2rem] p-6">
                    <h2 class="text-xl font-black text-slate-950 dark:text-white">
                        История улучшений
                    </h2>

                    <p class="mt-1 text-sm font-semibold text-slate-500 dark:text-slate-400">
                        Короткая динамика за последние 7 дней.
                    </p>

                    <div class="mt-5 grid gap-3 sm:grid-cols-2">
                        @foreach($improvementHistory as $item)
                            <div class="rounded-2xl bg-slate-50 p-4 ring-1 ring-slate-200/70 dark:bg-white/[0.04] dark:ring-white/10">
                                <p class="text-sm font-bold text-slate-500 dark:text-slate-400">{{ $item['label'] }}</p>
                                <p class="mt-2 text-3xl font-black text-slate-950 dark:text-white">{{ $item['value'] }}</p>
                                <p class="mt-1 text-xs font-black leading-4 text-slate-400 dark:text-slate-500">{{ $item['hint'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            <section class="mb-6 grid gap-6 xl:grid-cols-[0.9fr_1.1fr]">
                <div class="glass-panel rounded-[1.2rem] p-6">
                    <div class="mb-5 flex items-center justify-between gap-4">
                        <div>
                            <h2 class="text-xl font-black text-slate-950 dark:text-white">
                                Что сделать сейчас
                            </h2>
                            <p class="mt-1 text-sm font-semibold text-slate-500 dark:text-slate-400">
                                Короткий список ближайших учебных действий.
                            </p>
                        </div>

                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-500 dark:bg-white/5 dark:text-slate-400">
                            {{ $priorityActions->count() }} шага
                        </span>
                    </div>

                    <div class="grid gap-3">
                        @forelse($priorityActions as $action)
                            <div class="rounded-2xl bg-slate-50 p-4 ring-1 ring-slate-200/70 transition hover:bg-white dark:bg-white/[0.04] dark:ring-white/10 dark:hover:bg-white/[0.06]">
                                <p class="font-black text-slate-950 dark:text-white">
                                    {{ $action['title'] }}
                                </p>

                                <p class="mt-1 text-sm font-semibold leading-5 text-slate-500 dark:text-slate-400">
                                    {{ $action['description'] }}
                                </p>

                                <a href="{{ $action['route'] }}"
                                   class="mt-4 inline-flex rounded-xl bg-blue-600 px-4 py-2 text-sm font-black text-white shadow-lg shadow-blue-600/20 transition hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-400">
                                    {{ $action['label'] }}
                                </a>
                            </div>
                        @empty
                            <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center dark:border-white/10 dark:bg-white/[0.03]">
                                <p class="font-black text-slate-950 dark:text-white">
                                    Срочных действий нет
                                </p>
                                <p class="mt-2 text-sm font-semibold text-slate-500 dark:text-slate-400">
                                    Сейчас можно спокойно продолжать обучение по плану.
                                </p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="glass-panel rounded-[1.2rem] p-6">
                    <div class="mb-5 flex items-center justify-between gap-4">
                        <div>
                            <h2 class="text-xl font-black text-slate-950 dark:text-white">
                                Приоритетные тесты
                            </h2>
                            <p class="mt-1 text-sm font-semibold text-slate-500 dark:text-slate-400">
                                Тесты, которые лучше пройти в первую очередь.
                            </p>
                        </div>

                        <a href="{{ route('courses.index') }}"
                           class="shrink-0 rounded-xl border border-slate-200 px-3 py-2 text-sm font-black text-blue-600 transition hover:border-blue-400 hover:bg-blue-50 dark:border-white/10 dark:text-blue-300 dark:hover:bg-blue-500/10">
                            Все курсы
                        </a>
                    </div>

                    <div class="divide-y divide-slate-100 dark:divide-white/10">
                        @forelse($recommendedTests as $item)
                            <div class="flex flex-col gap-4 py-4 first:pt-0 last:pb-0 sm:flex-row sm:items-center sm:justify-between">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <p class="truncate font-black text-slate-950 dark:text-white">
                                            {{ $item['test']->title }}
                                        </p>

                                        <span class="rounded-full bg-blue-50 px-2.5 py-1 text-xs font-black text-blue-600 dark:bg-blue-500/15 dark:text-blue-300">
                                            {{ $item['status'] }}
                                        </span>
                                    </div>

                                    <p class="mt-1 truncate text-sm font-semibold text-slate-500 dark:text-slate-400">
                                        {{ $item['test']->course?->title ?? 'Без курса' }} · {{ $item['hint'] }}
                                    </p>
                                </div>

                                <div class="flex shrink-0 items-center gap-3">
                                    <p class="text-sm font-black text-slate-500 dark:text-slate-400">
                                        {{ $item['best_percent'] !== null ? $item['best_percent'] . '%' : 'нет попыток' }}
                                    </p>

                                    <a href="{{ route('tests.take', $item['test']) }}"
                                       class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-black text-slate-700 transition hover:border-blue-400 hover:text-blue-600 dark:border-white/10 dark:text-slate-200 dark:hover:text-blue-300">
                                        Открыть
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center dark:border-white/10 dark:bg-white/[0.03]">
                                <p class="font-black text-slate-950 dark:text-white">
                                    Нет доступных тестов
                                </p>
                                <p class="mt-2 text-sm font-semibold text-slate-500 dark:text-slate-400">
                                    Когда преподаватель опубликует тесты, куратор добавит их в план.
                                </p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </section>

            <section class="grid gap-6 xl:grid-cols-3">
                <div class="glass-panel rounded-[1.2rem] p-6 xl:col-span-2">
                    <div class="mb-5 flex items-center justify-between gap-4">
                        <div>
                            <h2 class="text-xl font-black text-slate-950 dark:text-white">
                                Рекомендованные материалы
                            </h2>
                            <p class="mt-1 text-sm font-semibold text-slate-500 dark:text-slate-400">
                                Материалы, которые помогут закрыть пробелы и подготовиться к тестам.
                            </p>
                        </div>

                        <span class="shrink-0 rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-500 dark:bg-white/5 dark:text-slate-400">
                            {{ $coursesCount }} курсов
                        </span>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        @forelse($recommendedMaterials as $material)
                            <a href="{{ route('materials.show', $material) }}"
                               class="group rounded-2xl bg-slate-50 p-4 ring-1 ring-slate-200/70 transition hover:-translate-y-0.5 hover:bg-white hover:shadow-lg hover:shadow-blue-100 dark:bg-white/[0.04] dark:ring-white/10 dark:hover:bg-white/[0.06] dark:hover:shadow-none">
                                <p class="text-xs font-black text-blue-600 dark:text-blue-300">
                                    {{ $material->course?->title ?? 'Без курса' }}
                                </p>

                                <p class="mt-2 line-clamp-2 font-black text-slate-950 transition group-hover:text-blue-600 dark:text-white dark:group-hover:text-blue-300">
                                    {{ $material->title }}
                                </p>

                                <p class="mt-2 line-clamp-2 text-sm font-semibold leading-5 text-slate-500 dark:text-slate-400">
                                    {{ $material->excerpt }}
                                </p>
                            </a>
                        @empty
                            <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center dark:border-white/10 dark:bg-white/[0.03] md:col-span-2">
                                <p class="font-black text-slate-950 dark:text-white">
                                    Материалов пока нет
                                </p>
                                <p class="mt-2 text-sm font-semibold text-slate-500 dark:text-slate-400">
                                    После публикации материалов они появятся в персональном плане.
                                </p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="glass-panel rounded-[1.2rem] p-6">
                    <h2 class="text-xl font-black text-slate-950 dark:text-white">
                        Ближайшие занятия
                    </h2>

                    <p class="mt-1 text-sm font-semibold text-slate-500 dark:text-slate-400">
                        События из расписания на ближайшее время.
                    </p>

                    <div class="mt-5 grid gap-3">
                        @forelse($upcomingEvents as $event)
                            <div class="rounded-2xl bg-slate-50 p-4 ring-1 ring-slate-200/70 dark:bg-white/[0.04] dark:ring-white/10">
                                <p class="text-sm font-black text-blue-600 dark:text-blue-300">
                                    {{ $event->starts_at->format('d.m.Y H:i') }}
                                </p>

                                <p class="mt-2 font-black text-slate-950 dark:text-white">
                                    {{ $event->title ?? $event->course?->title ?? 'Занятие' }}
                                </p>

                                <p class="mt-1 text-sm font-semibold text-slate-500 dark:text-slate-400">
                                    {{ $event->location ?: 'Место не указано' }}
                                </p>
                            </div>
                        @empty
                            <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center dark:border-white/10 dark:bg-white/[0.03]">
                                <p class="font-black text-slate-950 dark:text-white">
                                    Событий пока нет
                                </p>
                                <p class="mt-2 text-sm font-semibold text-slate-500 dark:text-slate-400">
                                    Проверь расписание позже.
                                </p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </section>

            <section class="mt-6 glass-panel rounded-[1.2rem] p-6">
                <div class="mb-5 flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-black text-slate-950 dark:text-white">
                            Прогресс по курсам
                        </h2>
                        <p class="mt-1 text-sm font-semibold text-slate-500 dark:text-slate-400">
                            Сводка выполнения тестов по каждому доступному курсу.
                        </p>
                    </div>

                    <a href="{{ route('results.my') }}"
                       class="shrink-0 rounded-xl border border-slate-200 px-3 py-2 text-sm font-black text-blue-600 transition hover:border-blue-400 hover:bg-blue-50 dark:border-white/10 dark:text-blue-300 dark:hover:bg-blue-500/10">
                        Мои результаты
                    </a>
                </div>

                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    @forelse($courseProgress as $item)
                        <div class="rounded-2xl bg-slate-50 p-4 ring-1 ring-slate-200/70 dark:bg-white/[0.04] dark:ring-white/10">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="truncate font-black text-slate-950 dark:text-white">
                                        {{ $item['course']->title }}
                                    </p>

                                    <p class="mt-1 text-sm font-semibold text-slate-500 dark:text-slate-400">
                                        {{ $item['completed_count'] }} из {{ $item['tests_count'] }} тестов · средний результат {{ $item['average_percent'] }}%
                                    </p>
                                </div>

                                <span class="shrink-0 rounded-full bg-blue-50 px-2.5 py-1 text-xs font-black text-blue-600 dark:bg-blue-500/15 dark:text-blue-300">
                                    {{ $item['progress_percent'] }}%
                                </span>
                            </div>

                            <div class="mt-4 h-2 overflow-hidden rounded-full bg-white dark:bg-white/5">
                                <div class="h-full rounded-full bg-blue-600 dark:bg-blue-400"
                                     style="width: {{ min(100, max(0, $item['progress_percent'])) }}%">
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center dark:border-white/10 dark:bg-white/[0.03] md:col-span-2 xl:col-span-3">
                            <p class="font-black text-slate-950 dark:text-white">
                                Курсов пока нет
                            </p>
                            <p class="mt-2 text-sm font-semibold text-slate-500 dark:text-slate-400">
                                Открой каталог курсов и начни обучение.
                            </p>
                        </div>
                    @endforelse
                </div>
            </section>

        </div>
    </div>
</x-app-layout>
