<x-app-layout>
    @php
        $modelQuality = $modelQuality ?? [];
        $featureColumns = collect($modelQuality['feature_columns'] ?? []);
        $featureImportance = collect($modelQuality['feature_importance'] ?? [])->filter(fn ($item) => ($item['importance'] ?? 0) > 0);
        $confusionMatrix = collect($modelQuality['confusion_matrix'] ?? []);
        $modelCache = $modelQuality['model_cache'] ?? [];

        $formatMetric = fn ($value) => $value === null || $value === ''
            ? 'Недостаточно данных'
            : round((float) $value, 3);

        $metricCards = [
            [
                'label' => 'Студентов в анализе',
                'value' => $studentsCount,
                'hint' => 'Текущая выборка',
                'icon' => '👥',
                'accent' => 'text-blue-600 dark:text-blue-300',
                'iconClass' => 'bg-blue-50 text-blue-600 dark:bg-blue-500/15 dark:text-blue-300',
            ],
            [
                'label' => 'Группа риска',
                'value' => $riskCount,
                'hint' => 'Требуют внимания',
                'icon' => '⚠️',
                'accent' => 'text-red-600 dark:text-red-300',
                'iconClass' => 'bg-red-50 text-red-600 dark:bg-red-500/15 dark:text-red-300',
            ],
            [
                'label' => 'Стабильные студенты',
                'value' => $stableCount,
                'hint' => 'Низкий риск',
                'icon' => '✓',
                'accent' => 'text-emerald-600 dark:text-emerald-300',
                'iconClass' => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-300',
            ],
            [
                'label' => 'Средний прогноз',
                'value' => $averageSuccessProbability . '%',
                'hint' => 'Вероятность успеха',
                'icon' => '📈',
                'accent' => 'text-violet-600 dark:text-violet-300',
                'iconClass' => 'bg-violet-50 text-violet-600 dark:bg-violet-500/15 dark:text-violet-300',
            ],
        ];

        $qualityItems = [
            'Студентов' => $modelQuality['students_count'] ?? $studentsCount,
            'Обучающих записей' => $modelQuality['training_samples_count'] ?? ($modelQuality['samples_count'] ?? $studentsCount),
            'Accuracy' => $formatMetric($modelQuality['accuracy'] ?? null),
            'Precision' => $formatMetric($modelQuality['precision'] ?? null),
            'Recall' => $formatMetric($modelQuality['recall'] ?? null),
            'F1-score' => $formatMetric($modelQuality['f1_score'] ?? null),
            'CV accuracy' => $formatMetric($modelQuality['cv_accuracy_mean'] ?? null),
            'CV folds' => $modelQuality['cv_folds'] ?? 0,
        ];

        $methods = [
            'Классификация' => 'Decision Tree определяет категорию студента по уровню образовательного риска.',
            'Прогнозирование' => 'Logistic Regression рассчитывает вероятность успешного прохождения обучения.',
            'Кластеризация' => 'Группировка студентов по активности и результатам.',
            'Кэширование модели' => 'Обученные ML-модели сохраняются в joblib-файлы и переиспользуются.',
            'Экспертная система' => 'Рекомендации и объяснения формируются по аналитическим правилам.',
        ];
    @endphp

    <div class="min-h-screen bg-[#f6f8fc] text-slate-950 transition-colors duration-300 dark:bg-[#07111f] dark:text-white">
        <div class="relative overflow-hidden">
            <div class="relative mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 lg:py-10">

                <section class="mb-7 grid gap-6 lg:grid-cols-[1fr_360px]">
                    <div>
                        <p class="mb-4 inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700 dark:border-blue-400/20 dark:bg-blue-500/10 dark:text-blue-200">
                            Интеллектуальный модуль
                        </p>

                        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                            <h1 class="text-3xl font-black tracking-tight text-slate-950 sm:text-4xl dark:text-white">
                                ИИ-аналитика образовательного процесса
                            </h1>

                            <button type="button"
                                    onclick="window.print()"
                                    class="print-hidden inline-flex w-fit items-center justify-center rounded-xl border border-slate-200 bg-white/70 px-4 py-2 text-sm font-black text-slate-700 transition hover:border-blue-400 hover:text-blue-600 dark:border-white/10 dark:bg-white/[0.06] dark:text-slate-200 dark:hover:text-blue-300">
                                Печать
                            </button>
                        </div>

                        <p class="mt-3 max-w-3xl text-sm font-semibold text-slate-500 sm:text-base dark:text-slate-300">
                            Анализ учебной активности студентов: классификация рисков, прогноз успешности,
                            кластеризация поведения и экспертные рекомендации для преподавателя.
                        </p>
                    </div>

                    <div class="glass-panel min-w-0 rounded-[1.2rem] p-5 dark:shadow-none">
                        <p class="text-sm font-bold text-slate-500 dark:text-slate-400">Режим работы модели</p>

                        <div class="mt-3 flex items-end justify-between gap-4">
                            <div class="min-w-0">
                                <p class="text-4xl font-black text-slate-950 dark:text-white">
                                    {{ ($modelQuality['mode'] ?? 'expert') === 'ml' ? 'ML' : 'Expert' }}
                                </p>
                                <p class="mt-1 max-w-[240px] text-xs font-black leading-5 text-blue-600 dark:text-blue-300">
                                    K-Means · Decision Tree · Logistic Regression
                                </p>
                            </div>

                            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-50 text-2xl text-blue-600 shadow-inner dark:bg-blue-500/15 dark:text-blue-300">
                                AI
                            </div>
                        </div>
                    </div>
                </section>

                <section class="mb-6 grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach($metricCards as $card)
                        <div class="glass-panel min-w-0 rounded-[1.2rem] p-5 transition hover:-translate-y-1 hover:shadow-xl hover:shadow-blue-100 dark:shadow-none">
                            <div class="flex items-center gap-4">
                                <div class="flex h-14 w-14 items-center justify-center rounded-2xl text-2xl shadow-inner {{ $card['iconClass'] }}">
                                    {{ $card['icon'] }}
                                </div>

                                <div class="min-w-0">
                                    <p class="break-words text-sm font-bold leading-5 text-slate-500 dark:text-slate-400">
                                        {{ $card['label'] }}
                                    </p>
                                    <p class="mt-1 text-4xl font-black {{ $card['accent'] }}">
                                        {{ $card['value'] }}
                                    </p>
                                    <p class="mt-1 break-words text-xs font-black leading-4 text-slate-400 dark:text-slate-500">
                                        {{ $card['hint'] }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </section>

                <section class="mb-6 glass-panel rounded-[1.2rem] p-6 dark:shadow-none">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div>
                            <h2 class="text-xl font-black text-slate-950 dark:text-white">
                                Качество модели
                            </h2>
                            <p class="mt-2 max-w-4xl text-sm font-semibold leading-6 text-slate-500 dark:text-slate-400">
                                {{ $modelQuality['explanation'] ?? 'Информация о качестве модели недоступна.' }}
                            </p>
                        </div>

                        <span class="inline-flex w-fit rounded-full px-3 py-1 text-xs font-black
                            {{ ($modelQuality['mode'] ?? 'expert') === 'ml'
                                ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-300'
                                : 'bg-amber-50 text-amber-600 dark:bg-amber-500/15 dark:text-amber-300' }}">
                            {{ ($modelQuality['mode'] ?? 'expert') === 'ml' ? 'ML-режим' : 'Экспертный режим' }}
                        </span>
                    </div>

                    <div class="mt-5 grid grid-cols-2 gap-3 md:grid-cols-4">
                        @foreach($qualityItems as $label => $value)
                            <div class="glass-chip min-w-0 rounded-xl px-4 py-3">
                                <p class="break-words text-xs font-bold leading-4 text-slate-500 dark:text-slate-400">
                                    {{ $label }}
                                </p>
                                <p class="mt-1 break-words text-base font-black text-slate-950 dark:text-white">
                                    {{ $value }}
                                </p>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-5 grid grid-cols-1 gap-4 lg:grid-cols-[1.3fr_0.7fr]">
                        <div class="glass-chip min-w-0 rounded-xl p-4">
                            <p class="text-sm font-black text-slate-950 dark:text-white">
                                Признаки модели
                            </p>

                            <div class="mt-3 flex flex-wrap gap-2">
                                @forelse($featureColumns as $feature)
                                    <span class="max-w-full break-words rounded-full bg-white px-3 py-1 text-xs font-bold leading-4 text-slate-600 ring-1 ring-slate-200 dark:bg-white/[0.04] dark:text-slate-300 dark:ring-white/10">
                                        {{ $feature['label'] ?? $feature['name'] }}
                                    </span>
                                @empty
                                    <span class="text-sm font-semibold text-slate-500 dark:text-slate-400">
                                        Признаки не переданы
                                    </span>
                                @endforelse
                            </div>
                        </div>

                        <div class="glass-chip min-w-0 rounded-xl p-4">
                            <p class="text-sm font-black text-slate-950 dark:text-white">
                                Кэш моделей
                            </p>

                            <div class="mt-3 grid gap-2 text-sm font-semibold text-slate-500 dark:text-slate-400">
                                <p class="break-words">
                                    Классификатор:
                                    <span class="font-black text-slate-950 dark:text-white">
                                        {{ data_get($modelCache, 'risk_classifier.status', 'нет данных') }}
                                    </span>
                                </p>
                                <p class="break-words">
                                    Прогноз:
                                    <span class="font-black text-slate-950 dark:text-white">
                                        {{ data_get($modelCache, 'success_predictor.status', 'нет данных') }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-2">
                        <div class="glass-chip min-w-0 rounded-xl p-4">
                            <p class="text-sm font-black text-slate-950 dark:text-white">
                                Важность признаков
                            </p>

                            <div class="mt-4 grid gap-3">
                                @forelse($featureImportance as $feature)
                                    <div>
                                        <div class="mb-1 flex items-center justify-between gap-3 text-xs font-bold">
                                            <span class="min-w-0 break-words text-slate-600 dark:text-slate-300">
                                                {{ $feature['label'] ?? $feature['name'] }}
                                            </span>
                                            <span class="text-blue-600 dark:text-blue-300">
                                                {{ round(($feature['importance'] ?? 0) * 100, 1) }}%
                                            </span>
                                        </div>

                                        <div class="h-2 overflow-hidden rounded-full bg-slate-100 dark:bg-white/5">
                                            <div class="h-full rounded-full bg-blue-600 dark:bg-blue-400"
                                                 style="width: {{ min(100, max(0, ($feature['importance'] ?? 0) * 100)) }}%">
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">
                                        Важность доступна после обучения Decision Tree.
                                    </p>
                                @endforelse
                            </div>
                        </div>

                        <div class="glass-chip min-w-0 rounded-xl p-4">
                            <p class="text-sm font-black text-slate-950 dark:text-white">
                                Матрица ошибок
                            </p>

                            <div class="mt-4 overflow-x-auto">
                                <table class="w-full min-w-[420px] text-xs">
                                    <thead class="text-slate-500 dark:text-slate-400">
                                        <tr>
                                            <th class="px-2 py-2 text-left font-black">Факт \ прогноз</th>
                                            <th class="px-2 py-2 text-left font-black">Низкий</th>
                                            <th class="px-2 py-2 text-left font-black">Средний</th>
                                            <th class="px-2 py-2 text-left font-black">Высокий</th>
                                        </tr>
                                    </thead>

                                    <tbody class="divide-y divide-slate-100 dark:divide-white/10">
                                        @forelse($confusionMatrix as $row)
                                            <tr>
                                                <td class="px-2 py-2 font-black text-slate-950 dark:text-white">
                                                    {{ $row['actual'] }}
                                                </td>
                                                <td class="px-2 py-2 font-semibold">
                                                    {{ data_get($row, 'predicted.Низкий риск', 0) }}
                                                </td>
                                                <td class="px-2 py-2 font-semibold">
                                                    {{ data_get($row, 'predicted.Средний риск', 0) }}
                                                </td>
                                                <td class="px-2 py-2 font-semibold">
                                                    {{ data_get($row, 'predicted.Высокий риск', 0) }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="px-2 py-4 text-sm font-semibold text-slate-500 dark:text-slate-400">
                                                    Матрица доступна после ML-обучения.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="mb-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
                    <div class="glass-panel min-w-0 rounded-[1.2rem] p-6 dark:shadow-none lg:col-span-2">
                        <h2 class="text-xl font-black text-slate-950 dark:text-white">
                            Экспертные рекомендации
                        </h2>

                        <div class="mt-5 grid gap-3">
                            @forelse($expertRecommendations as $recommendation)
                                <div class="min-w-0 rounded-xl border px-4 py-3
                                    @if($recommendation['type'] === 'danger') border-red-200 bg-red-50 text-red-800 dark:border-red-400/20 dark:bg-red-500/10 dark:text-red-200
                                    @elseif($recommendation['type'] === 'warning') border-amber-200 bg-amber-50 text-amber-800 dark:border-amber-400/20 dark:bg-amber-500/10 dark:text-amber-200
                                    @elseif($recommendation['type'] === 'success') border-emerald-200 bg-emerald-50 text-emerald-800 dark:border-emerald-400/20 dark:bg-emerald-500/10 dark:text-emerald-200
                                    @else border-blue-200 bg-blue-50 text-blue-800 dark:border-blue-400/20 dark:bg-blue-500/10 dark:text-blue-200
                                    @endif">
                                    <p class="break-words font-black">
                                        {{ $recommendation['title'] }}
                                    </p>
                                    <p class="mt-1 break-words text-sm font-semibold leading-6 opacity-80">
                                        {{ $recommendation['description'] }}
                                    </p>
                                </div>
                            @empty
                                <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center dark:border-white/10 dark:bg-white/[0.03]">
                                    <p class="font-black text-slate-950 dark:text-white">
                                        Рекомендаций пока нет
                                    </p>
                                    <p class="mt-2 text-sm font-semibold text-slate-500 dark:text-slate-400">
                                        Система не выявила выраженных образовательных рисков.
                                    </p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <div class="glass-panel min-w-0 rounded-[1.2rem] p-6 dark:shadow-none">
                        <h2 class="text-xl font-black text-slate-950 dark:text-white">
                            Методы анализа
                        </h2>

                        <div class="mt-5 grid gap-3">
                            @foreach($methods as $method => $description)
                                <div class="glass-chip min-w-0 rounded-xl px-4 py-3">
                                    <p class="break-words font-black text-blue-600 dark:text-blue-300">
                                        {{ $method }}
                                    </p>
                                    <p class="mt-1 break-words text-sm font-semibold leading-6 text-slate-500 dark:text-slate-400">
                                        {{ $description }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </section>

                <section data-ai-students-section class="mb-6 overflow-hidden rounded-[1.7rem] border border-white bg-white shadow-sm shadow-slate-200/70 dark:border-white/10 dark:bg-white/[0.04] dark:shadow-none">
                    <div class="border-b border-slate-100 px-6 py-5 dark:border-white/10">
                        <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                            <div class="min-w-0">
                                <h2 class="text-xl font-black text-slate-950 dark:text-white">
                                    Классификация и прогнозирование студентов
                                </h2>
                                <p class="mt-1 text-sm font-semibold text-slate-500 dark:text-slate-400">
                                    Карточки показывают риск, прогноз успешности, учебную активность и конкретные причины результата.
                                </p>
                            </div>

                            <div class="flex w-full flex-col gap-3 sm:flex-row xl:w-auto xl:min-w-[520px]">
                                <label class="min-w-0 flex-1">
                                    <span class="sr-only">Поиск студента</span>
                                    <input
                                        type="search"
                                        data-ai-students-search
                                        class="h-11 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-blue-300 focus:ring-4 focus:ring-blue-100 dark:border-white/10 dark:bg-white/[0.04] dark:text-slate-200 dark:focus:border-blue-400/40 dark:focus:ring-blue-500/10"
                                        placeholder="Поиск по студенту, группе, риску..."
                                        autocomplete="off"
                                    >
                                </label>

                                <button
                                    type="button"
                                    data-ai-students-toggle
                                    class="inline-flex h-11 shrink-0 items-center justify-center rounded-xl bg-blue-600 px-4 text-sm font-black text-white transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-100 dark:bg-blue-500 dark:hover:bg-blue-400 dark:focus:ring-blue-500/20"
                                    aria-expanded="true"
                                >
                                    Свернуть
                                </button>
                            </div>
                        </div>

                        <div class="mt-3 flex flex-wrap items-center gap-2 text-xs font-black text-slate-400 dark:text-slate-500">
                            <span>
                                Найдено: <span data-ai-students-visible>{{ $studentAnalytics->count() }}</span> из {{ $studentAnalytics->count() }}
                            </span>
                        </div>
                    </div>

                    <div data-ai-students-body class="grid gap-4 p-5">
                        @forelse($studentAnalytics as $item)
                            @php
                                $riskLevel = $item['risk_level'] ?? 'Низкий риск';
                                $successProbability = min(100, max(0, (float) ($item['success_probability'] ?? 0)));
                                $averageScore = $item['average_score'] ?? 0;
                                $completionPercent = min(100, max(0, (float) ($item['completion_percent'] ?? 0)));
                                $activityScore = min(100, max(0, (float) ($item['activity_score'] ?? 0)));

                                $riskClasses = match ($riskLevel) {
                                    'Высокий риск' => [
                                        'card' => 'border-red-200 bg-red-50/60 dark:border-red-400/20 dark:bg-red-500/[0.06]',
                                        'badge' => 'bg-red-100 text-red-700 ring-red-200 dark:bg-red-500/15 dark:text-red-300 dark:ring-red-400/20',
                                        'dot' => 'bg-red-500',
                                        'bar' => 'bg-red-500 dark:bg-red-400',
                                    ],
                                    'Средний риск' => [
                                        'card' => 'border-amber-200 bg-amber-50/60 dark:border-amber-400/20 dark:bg-amber-500/[0.06]',
                                        'badge' => 'bg-amber-100 text-amber-700 ring-amber-200 dark:bg-amber-500/15 dark:text-amber-300 dark:ring-amber-400/20',
                                        'dot' => 'bg-amber-500',
                                        'bar' => 'bg-amber-500 dark:bg-amber-400',
                                    ],
                                    default => [
                                        'card' => 'border-emerald-200 bg-emerald-50/60 dark:border-emerald-400/20 dark:bg-emerald-500/[0.06]',
                                        'badge' => 'bg-emerald-100 text-emerald-700 ring-emerald-200 dark:bg-emerald-500/15 dark:text-emerald-300 dark:ring-emerald-400/20',
                                        'dot' => 'bg-emerald-500',
                                        'bar' => 'bg-emerald-500 dark:bg-emerald-400',
                                    ],
                                };

                                $studentSearchText = collect([
                                    $item['student']->name,
                                    $item['student']->classGroup?->name ?? 'Без группы',
                                    $riskLevel,
                                    $item['category'] ?? '',
                                    $item['recommendation'] ?? '',
                                ])
                                    ->merge(collect($item['risk_factors'] ?? [])->flatMap(fn ($factor) => [
                                        $factor['factor'] ?? '',
                                        $factor['value'] ?? '',
                                        $factor['impact'] ?? '',
                                    ]))
                                    ->filter()
                                    ->implode(' ');
                            @endphp

                            <article data-ai-student-card data-search="{{ $studentSearchText }}" class="min-w-0 overflow-hidden rounded-2xl border {{ $riskClasses['card'] }}">
                                <div class="grid gap-0 lg:grid-cols-[1fr_430px]">
                                    <div class="min-w-0 p-5">
                                        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                            <div class="min-w-0">
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <span class="h-3 w-3 rounded-full {{ $riskClasses['dot'] }}"></span>

                                                    <h3 class="break-words text-lg font-black text-slate-950 dark:text-white">
                                                        {{ $item['student']->name }}
                                                    </h3>

                                                    <span class="rounded-full bg-white px-3 py-1 text-xs font-black text-slate-500 ring-1 ring-slate-200 dark:bg-white/[0.04] dark:text-slate-300 dark:ring-white/10">
                                                        {{ $item['student']->classGroup?->name ?? 'Без группы' }}
                                                    </span>
                                                </div>

                                                <p class="mt-2 break-words text-sm font-semibold leading-6 text-slate-600 dark:text-slate-300">
                                                    {{ $item['category'] ?? 'Категория не определена' }}
                                                </p>
                                            </div>

                                            <span class="w-fit rounded-full px-3 py-1 text-xs font-black ring-1 {{ $riskClasses['badge'] }}">
                                                {{ $riskLevel }}
                                            </span>
                                        </div>

                                        <div class="mt-5 grid grid-cols-2 gap-3 xl:grid-cols-4">
                                            <div class="rounded-xl bg-white px-4 py-3 ring-1 ring-slate-200 dark:bg-white/[0.04] dark:ring-white/10">
                                                <p class="text-[11px] font-black uppercase tracking-wide text-slate-400">
                                                    Средний балл
                                                </p>
                                                <p class="mt-1 text-xl font-black text-slate-950 dark:text-white">
                                                    {{ $averageScore }}
                                                </p>
                                            </div>

                                            <div class="rounded-xl bg-white px-4 py-3 ring-1 ring-slate-200 dark:bg-white/[0.04] dark:ring-white/10">
                                                <p class="text-[11px] font-black uppercase tracking-wide text-slate-400">
                                                    Завершено
                                                </p>
                                                <p class="mt-1 text-xl font-black text-slate-950 dark:text-white">
                                                    {{ $completionPercent }}%
                                                </p>
                                            </div>

                                            <div class="rounded-xl bg-white px-4 py-3 ring-1 ring-slate-200 dark:bg-white/[0.04] dark:ring-white/10">
                                                <p class="text-[11px] font-black uppercase tracking-wide text-slate-400">
                                                    Активность
                                                </p>
                                                <p class="mt-1 text-xl font-black text-slate-950 dark:text-white">
                                                    {{ $activityScore }}%
                                                </p>
                                            </div>

                                            <div class="rounded-xl bg-white px-4 py-3 ring-1 ring-slate-200 dark:bg-white/[0.04] dark:ring-white/10">
                                                <p class="text-[11px] font-black uppercase tracking-wide text-slate-400">
                                                    Прогноз
                                                </p>
                                                <p class="mt-1 text-xl font-black text-blue-600 dark:text-blue-300">
                                                    {{ $successProbability }}%
                                                </p>
                                            </div>
                                        </div>

                                        <div class="mt-5">
                                            <div class="mb-2 flex items-center justify-between gap-3 text-xs font-black">
                                                <span class="text-slate-500 dark:text-slate-400">
                                                    Вероятность успешного прохождения
                                                </span>
                                                <span class="text-blue-600 dark:text-blue-300">
                                                    {{ $successProbability }}%
                                                </span>
                                            </div>

                                            <div class="h-3 overflow-hidden rounded-full bg-white ring-1 ring-slate-200 dark:bg-white/[0.05] dark:ring-white/10">
                                                <div class="h-full rounded-full bg-blue-600 dark:bg-blue-400"
                                                     style="width: {{ $successProbability }}%">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="border-t border-white/70 bg-white/70 p-5 dark:border-white/10 dark:bg-white/[0.03] lg:border-l lg:border-t-0">
                                        <div>
                                            <p class="text-xs font-black uppercase tracking-wide text-slate-400">
                                                Факторы риска
                                            </p>

                                            <div class="mt-3 grid gap-2">
                                                @forelse(($item['risk_factors'] ?? []) as $factor)
                                                    <div class="rounded-xl bg-white px-3 py-3 ring-1 ring-slate-200 dark:bg-white/[0.04] dark:ring-white/10">
                                                        <p class="break-words text-sm font-black text-slate-950 dark:text-white">
                                                            {{ $factor['factor'] ?? 'Фактор' }}
                                                            @if(!empty($factor['value']))
                                                                <span class="text-slate-400">—</span>
                                                                <span class="text-blue-600 dark:text-blue-300">
                                                                    {{ $factor['value'] }}
                                                                </span>
                                                            @endif
                                                        </p>

                                                        @if(!empty($factor['impact']))
                                                            <p class="mt-1 break-words text-xs font-semibold leading-5 text-slate-500 dark:text-slate-400">
                                                                {{ $factor['impact'] }}
                                                            </p>
                                                        @endif
                                                    </div>
                                                @empty
                                                    <div class="rounded-xl border border-dashed border-slate-300 bg-white/60 px-3 py-4 text-sm font-semibold text-slate-500 dark:border-white/10 dark:bg-white/[0.03] dark:text-slate-400">
                                                        Выраженные факторы риска не выявлены.
                                                    </div>
                                                @endforelse
                                            </div>
                                        </div>

                                        <div class="mt-4 rounded-xl bg-blue-50 px-4 py-3 ring-1 ring-blue-100 dark:bg-blue-500/10 dark:ring-blue-400/20">
                                            <p class="text-xs font-black uppercase tracking-wide text-blue-600 dark:text-blue-300">
                                                Рекомендация
                                            </p>
                                            <p class="mt-2 break-words text-sm font-semibold leading-6 text-slate-700 dark:text-slate-300">
                                                {{ $item['recommendation'] ?? 'Рекомендация пока не сформирована.' }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <div class="px-5 py-12 text-center">
                                <p class="font-black text-slate-950 dark:text-white">
                                    Нет данных для анализа
                                </p>
                                <p class="mt-2 text-sm font-semibold text-slate-500 dark:text-slate-400">
                                    После появления попыток тестирования здесь отобразятся прогнозы по студентам.
                                </p>
                            </div>
                        @endforelse

                        <div data-ai-students-empty class="hidden rounded-2xl border border-dashed border-slate-300 px-5 py-12 text-center dark:border-white/10">
                            <p class="font-black text-slate-950 dark:text-white">
                                Студенты не найдены
                            </p>
                            <p class="mt-2 text-sm font-semibold text-slate-500 dark:text-slate-400">
                                Измените поисковый запрос.
                            </p>
                        </div>
                    </div>
                </section>

                <section class="mb-6">
                    <div class="mb-5">
                        <h2 class="text-xl font-black text-slate-950 dark:text-white">
                            Кластеризация студентов
                        </h2>
                        <p class="mt-1 text-sm font-semibold text-slate-500 dark:text-slate-400">
                            Студенты распределяются по группам на основе успеваемости и активности.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                        @forelse($clusteredStudents as $clusterName => $students)
                            <div class="glass-panel rounded-[1.7rem] p-6 dark:shadow-none">
                                <div class="mb-5 flex items-start justify-between gap-4">
                                    <div>
                                        <h3 class="text-lg font-black text-slate-950 dark:text-white">
                                            {{ $clusterName }}
                                        </h3>
                                        <p class="mt-1 text-sm font-semibold text-slate-500 dark:text-slate-400">
                                            {{ $students->first()['cluster_description'] ?? 'Смешанная группа студентов' }}
                                        </p>
                                    </div>

                                    <span class="shrink-0 rounded-full bg-blue-50 px-3 py-1 text-xs font-black text-blue-600 dark:bg-blue-500/15 dark:text-blue-300">
                                        {{ $students->count() }} чел.
                                    </span>
                                </div>

                                <div class="grid gap-3">
                                    @foreach($students as $item)
                                        <div class="glass-chip flex items-center justify-between gap-4 rounded-2xl p-4">
                                            <div class="min-w-0">
                                                <p class="truncate font-black text-slate-950 dark:text-white">
                                                    {{ $item['student']->name }}
                                                </p>
                                                <p class="mt-1 text-xs font-semibold text-slate-500 dark:text-slate-400">
                                                    Балл: {{ $item['average_score'] }} · Активность: {{ $item['activity_score'] }}%
                                                </p>
                                            </div>

                                            <span class="shrink-0 text-sm font-black text-blue-600 dark:text-blue-300">
                                                {{ $item['success_probability'] }}%
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @empty
                            <div class="glass-panel rounded-[1.7rem] p-8 text-center dark:shadow-none">
                                <p class="font-black text-slate-950 dark:text-white">
                                    Нет данных для кластеризации
                                </p>
                            </div>
                        @endforelse
                    </div>
                </section>

                <section class="overflow-hidden rounded-[1.7rem] border border-white bg-white shadow-sm shadow-slate-200/70 dark:border-white/10 dark:bg-white/[0.04] dark:shadow-none">
                    <div class="border-b border-slate-100 px-6 py-5 dark:border-white/10">
                        <h2 class="text-xl font-black text-slate-950 dark:text-white">
                            Анализ сложности тестов
                        </h2>
                        <p class="mt-1 text-sm font-semibold text-slate-500 dark:text-slate-400">
                            Система определяет проблемные тесты по среднему проценту выполнения.
                        </p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[820px] text-sm">
                            <thead class="border-b border-slate-100 bg-slate-50 text-slate-500 dark:border-white/10 dark:bg-white/[0.03] dark:text-slate-400">
                                <tr>
                                    <th class="px-5 py-4 text-left font-black">Тест</th>
                                    <th class="px-5 py-4 text-left font-black">Курс</th>
                                    <th class="px-5 py-4 text-left font-black">Попытки</th>
                                    <th class="px-5 py-4 text-left font-black">Средний результат</th>
                                    <th class="px-5 py-4 text-left font-black">Сложность</th>
                                    <th class="px-5 py-4 text-left font-black">Рекомендация</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-slate-100 dark:divide-white/10">
                                @forelse($testAnalytics as $item)
                                    <tr class="transition hover:bg-slate-50 dark:hover:bg-white/[0.03]">
                                        <td class="px-5 py-4 font-black text-slate-950 dark:text-white">
                                            {{ data_get($item, 'test.title', 'Тест удалён') }}
                                        </td>

                                        <td class="px-5 py-4 font-semibold text-slate-500 dark:text-slate-400">
                                            {{ data_get($item, 'test.course.title', 'Без курса') }}
                                        </td>

                                        <td class="px-5 py-4 font-semibold text-slate-600 dark:text-slate-300">
                                            {{ $item['attempts_count'] }}
                                        </td>

                                        <td class="px-5 py-4 font-black text-slate-950 dark:text-white">
                                            {{ $item['average_percent'] }}%
                                        </td>

                                        <td class="px-5 py-4">
                                            <span class="rounded-full px-3 py-1 text-xs font-black
                                                @if($item['difficulty_level'] === 'Сложный') bg-red-50 text-red-600 dark:bg-red-500/15 dark:text-red-300
                                                @elseif($item['difficulty_level'] === 'Средний') bg-amber-50 text-amber-600 dark:bg-amber-500/15 dark:text-amber-300
                                                @else bg-emerald-50 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-300
                                                @endif">
                                                {{ $item['difficulty_level'] }}
                                            </span>
                                        </td>

                                        <td class="max-w-md px-5 py-4 text-sm font-semibold leading-6 text-slate-500 dark:text-slate-400">
                                            {{ $item['recommendation'] }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-5 py-12 text-center">
                                            <p class="font-black text-slate-950 dark:text-white">
                                                Нет тестов для анализа
                                            </p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>

            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const section = document.querySelector('[data-ai-students-section]');

            if (!section) {
                return;
            }

            const body = section.querySelector('[data-ai-students-body]');
            const search = section.querySelector('[data-ai-students-search]');
            const toggle = section.querySelector('[data-ai-students-toggle]');
            const visibleCounter = section.querySelector('[data-ai-students-visible]');
            const emptyState = section.querySelector('[data-ai-students-empty]');
            const cards = Array.from(section.querySelectorAll('[data-ai-student-card]'));

            const setExpanded = (expanded) => {
                body?.classList.toggle('hidden', !expanded);
                toggle?.setAttribute('aria-expanded', expanded ? 'true' : 'false');

                if (toggle) {
                    toggle.textContent = expanded ? 'Свернуть' : 'Развернуть';
                }
            };

            const applySearch = () => {
                const query = (search?.value || '').trim().toLocaleLowerCase('ru-RU');
                let visible = 0;

                cards.forEach((card) => {
                    const haystack = (card.dataset.search || '').toLocaleLowerCase('ru-RU');
                    const matched = query === '' || haystack.includes(query);

                    card.classList.toggle('hidden', !matched);

                    if (matched) {
                        visible += 1;
                    }
                });

                if (visibleCounter) {
                    visibleCounter.textContent = String(visible);
                }

                emptyState?.classList.toggle('hidden', visible !== 0 || cards.length === 0);

                if (query !== '') {
                    setExpanded(true);
                }
            };

            toggle?.addEventListener('click', () => {
                setExpanded(toggle.getAttribute('aria-expanded') !== 'true');
            });

            search?.addEventListener('input', applySearch);
            applySearch();
        });
    </script>
</x-app-layout>
