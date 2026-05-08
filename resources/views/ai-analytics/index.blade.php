<x-app-layout>
    <div class="min-h-screen bg-[#06191b] py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="mb-8">
                <p class="text-sm uppercase tracking-[0.25em] text-cyan-300/70">
                    Интеллектуальный модуль
                </p>

                <h1 class="text-4xl font-bold text-white mt-2">
                    ИИ-аналитика образовательного процесса
                </h1>

                <p class="text-slate-300 mt-3 max-w-3xl">
                    Классификация студентов, прогнозирование успешности, кластеризация учебного поведения
                    и экспертные рекомендации для преподавателя.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-5 mb-8">
                <div class="bg-white/10 border border-white/10 rounded-xl p-6">
                    <p class="text-slate-300 text-sm">Студентов в анализе</p>
                    <p class="text-4xl font-bold text-white mt-2">{{ $studentsCount }}</p>
                </div>

                <div class="bg-red-500/10 border border-red-500/20 rounded-xl p-6">
                    <p class="text-red-200 text-sm">Группа риска</p>
                    <p class="text-4xl font-bold text-white mt-2">{{ $riskCount }}</p>
                </div>

                <div class="bg-emerald-500/10 border border-emerald-500/20 rounded-xl p-6">
                    <p class="text-emerald-200 text-sm">Стабильные студенты</p>
                    <p class="text-4xl font-bold text-white mt-2">{{ $stableCount }}</p>
                </div>

                <div class="bg-cyan-500/10 border border-cyan-500/20 rounded-xl p-6">
                    <p class="text-cyan-200 text-sm">Средний прогноз</p>
                    <p class="text-4xl font-bold text-white mt-2">{{ $averageSuccessProbability }}%</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                <div class="lg:col-span-2 bg-white/10 border border-white/10 rounded-xl p-6">
                    <h2 class="text-xl font-bold text-white mb-4">
                        Экспертные рекомендации
                    </h2>

                    <div class="space-y-3">
                        @forelse($expertRecommendations as $recommendation)
                            <div class="rounded-xl border p-4
                                @if($recommendation['type'] === 'danger') bg-red-500/10 border-red-500/20
                                @elseif($recommendation['type'] === 'warning') bg-amber-500/10 border-amber-500/20
                                @elseif($recommendation['type'] === 'success') bg-emerald-500/10 border-emerald-500/20
                                @else bg-cyan-500/10 border-cyan-500/20
                                @endif">

                                <p class="font-semibold text-white">
                                    {{ $recommendation['title'] }}
                                </p>

                                <p class="text-sm text-slate-300 mt-1">
                                    {{ $recommendation['description'] }}
                                </p>
                            </div>
                        @empty
                            <p class="text-slate-400">
                                Критических рекомендаций нет. Система работает стабильно.
                            </p>
                        @endforelse
                    </div>
                </div>

                <div class="bg-white/10 border border-white/10 rounded-xl p-6">
                    <h2 class="text-xl font-bold text-white mb-4">
                        Что использует ИИ-модуль
                    </h2>

                    <div class="space-y-3 text-sm text-slate-300">
                        <div class="border border-white/10 rounded-lg p-3">
                            <p class="font-semibold text-cyan-200">Классификация</p>
                            <p>Определение категории студента по уровню риска.</p>
                        </div>

                        <div class="border border-white/10 rounded-lg p-3">
                            <p class="font-semibold text-cyan-200">Прогнозирование</p>
                            <p>Расчёт вероятности успешного прохождения обучения.</p>
                        </div>

                        <div class="border border-white/10 rounded-lg p-3">
                            <p class="font-semibold text-cyan-200">Кластеризация</p>
                            <p>Группировка студентов по активности и результатам.</p>
                        </div>

                        <div class="border border-white/10 rounded-lg p-3">
                            <p class="font-semibold text-cyan-200">Экспертная система</p>
                            <p>Формирование рекомендаций по правилам если-то.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white/10 border border-white/10 rounded-xl overflow-hidden mb-8">
                <div class="p-6 border-b border-white/10">
                    <h2 class="text-xl font-bold text-white">
                        Классификация и прогнозирование студентов
                    </h2>
                    <p class="text-sm text-slate-400 mt-1">
                        Анализ основан на баллах, активности, завершённых тестах, обращениях и давности последней попытки.
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-white/10 text-slate-300">
                            <tr>
                                <th class="px-4 py-3 text-left">Студент</th>
                                <th class="px-4 py-3 text-left">Группа</th>
                                <th class="px-4 py-3 text-left">Средний балл</th>
                                <th class="px-4 py-3 text-left">Завершено</th>
                                <th class="px-4 py-3 text-left">Активность</th>
                                <th class="px-4 py-3 text-left">Прогноз</th>
                                <th class="px-4 py-3 text-left">Риск</th>
                                <th class="px-4 py-3 text-left">Категория</th>
                                <th class="px-4 py-3 text-left">Рекомендация</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-white/10">
                            @forelse($studentAnalytics as $item)
                                <tr class="text-slate-200 hover:bg-white/5">
                                    <td class="px-4 py-4 font-semibold">
                                        {{ $item['student']->name }}
                                    </td>

                                    <td class="px-4 py-4 text-slate-400">
                                        {{ $item['student']->classGroup?->name ?? 'Без группы' }}
                                    </td>

                                    <td class="px-4 py-4">
                                        {{ $item['average_score'] }}
                                    </td>

                                    <td class="px-4 py-4">
                                        {{ $item['completion_percent'] }}%
                                    </td>

                                    <td class="px-4 py-4">
                                        {{ $item['activity_score'] }}%
                                    </td>

                                    <td class="px-4 py-4">
                                        <div class="w-24 h-2 bg-white/10 rounded-full overflow-hidden">
                                            <div class="h-full bg-cyan-400 rounded-full" style="width: {{ $item['success_probability'] }}%"></div>
                                        </div>
                                        <span class="text-xs text-slate-400">{{ $item['success_probability'] }}%</span>
                                    </td>

                                    <td class="px-4 py-4">
                                        <span class="px-3 py-1 rounded-lg text-xs font-semibold
                                            @if($item['risk_level'] === 'Высокий риск') bg-red-500/20 text-red-200
                                            @elseif($item['risk_level'] === 'Средний риск') bg-amber-500/20 text-amber-200
                                            @else bg-emerald-500/20 text-emerald-200
                                            @endif">
                                            {{ $item['risk_level'] }}
                                        </span>
                                    </td>

                                    <td class="px-4 py-4">
                                        {{ $item['category'] }}
                                    </td>

                                    <td class="px-4 py-4 text-slate-300 max-w-sm">
                                        {{ $item['recommendation'] }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-4 py-8 text-center text-slate-400">
                                        Нет данных для анализа.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mb-8">
                <div class="mb-4">
                    <h2 class="text-xl font-bold text-white">
                        Кластеризация студентов
                    </h2>
                    <p class="text-sm text-slate-400 mt-1">
                        Студенты распределяются по группам на основе успеваемости и активности.
                    </p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                    @forelse($clusteredStudents as $clusterName => $students)
                        <div class="bg-white/10 border border-white/10 rounded-xl p-6">
                            <div class="flex items-start justify-between gap-4 mb-4">
                                <div>
                                    <h3 class="text-lg font-bold text-white">
                                        {{ $clusterName }}
                                    </h3>

                                    <p class="text-sm text-slate-400 mt-1">
                                        @if($clusterName === 'Кластер 1')
                                            Активные и успешные студенты
                                        @elseif($clusterName === 'Кластер 2')
                                            Активные, но с низкими результатами
                                        @elseif($clusterName === 'Кластер 3')
                                            Успешные, но недостаточно активные
                                        @else
                                            Пассивные или проблемные студенты
                                        @endif
                                    </p>
                                </div>

                                <span class="px-3 py-1 rounded-lg bg-cyan-500/10 text-cyan-200 text-sm">
                                    {{ $students->count() }} чел.
                                </span>
                            </div>

                            <div class="space-y-2">
                                @foreach($students as $item)
                                    <div class="flex items-center justify-between gap-3 border border-white/10 rounded-lg px-3 py-2">
                                        <div>
                                            <p class="text-white font-medium">
                                                {{ $item['student']->name }}
                                            </p>
                                            <p class="text-xs text-slate-400">
                                                Балл: {{ $item['average_score'] }} /
                                                Активность: {{ $item['activity_score'] }}%
                                            </p>
                                        </div>

                                        <span class="text-sm text-slate-300">
                                            {{ $item['success_probability'] }}%
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <div class="bg-white/10 border border-white/10 rounded-xl p-6 text-slate-400">
                            Нет данных для кластеризации.
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="bg-white/10 border border-white/10 rounded-xl overflow-hidden">
                <div class="p-6 border-b border-white/10">
                    <h2 class="text-xl font-bold text-white">
                        Анализ сложности тестов
                    </h2>
                    <p class="text-sm text-slate-400 mt-1">
                        Система определяет проблемные тесты по среднему проценту выполнения.
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-white/10 text-slate-300">
                            <tr>
                                <th class="px-4 py-3 text-left">Тест</th>
                                <th class="px-4 py-3 text-left">Курс</th>
                                <th class="px-4 py-3 text-left">Попытки</th>
                                <th class="px-4 py-3 text-left">Средний результат</th>
                                <th class="px-4 py-3 text-left">Сложность</th>
                                <th class="px-4 py-3 text-left">Рекомендация</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-white/10">
                            @forelse($testAnalytics as $item)
                                <tr class="text-slate-200 hover:bg-white/5">
                                    <td class="px-4 py-4 font-semibold">
                                        {{ data_get($item, 'test.title', 'Тест удалён') }}
                                    </td>

                                    <td class="px-4 py-4 text-slate-400">
                                        {{ data_get($item, 'test.course.title', 'Без курса') }}
                                    </td>

                                    <td class="px-4 py-4">
                                        {{ $item['attempts_count'] }}
                                    </td>

                                    <td class="px-4 py-4">
                                        {{ $item['average_percent'] }}%
                                    </td>

                                    <td class="px-4 py-4">
                                        <span class="px-3 py-1 rounded-lg text-xs font-semibold
                                            @if($item['difficulty_level'] === 'Сложный') bg-red-500/20 text-red-200
                                            @elseif($item['difficulty_level'] === 'Средний') bg-amber-500/20 text-amber-200
                                            @else bg-emerald-500/20 text-emerald-200
                                            @endif">
                                            {{ $item['difficulty_level'] }}
                                        </span>
                                    </td>

                                    <td class="px-4 py-4 text-slate-300">
                                        {{ $item['recommendation'] }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-slate-400">
                                        Нет тестов для анализа.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
