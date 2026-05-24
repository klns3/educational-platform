@php
    $labels = [
        'heading' => 'Редактирование материала',
        'subtitle' => 'Курс:',
        'submit' => 'Обновить материал',
        'cancel' => 'Назад',
    ];
@endphp

<x-app-layout>
    <div class="min-h-screen bg-[#f6f8fc] text-slate-950 transition-colors duration-300 dark:bg-[#07111f] dark:text-white">
        <div class="relative overflow-hidden">

            <div class="relative mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
                <section class="mb-7">
                    <p class="mb-4 inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700 dark:border-blue-400/20 dark:bg-blue-500/10 dark:text-blue-200">
                        Материалы
                    </p>

                    <h1 class="text-3xl font-black tracking-tight text-slate-950 sm:text-4xl dark:text-white">
                        {{ $labels['heading'] }}
                    </h1>

                    <p class="mt-3 max-w-2xl text-sm font-semibold text-slate-500 sm:text-base dark:text-slate-300">
                        {{ $labels['subtitle'] }} {{ $material->course->title }}
                    </p>
                </section>

                @include('materials.form', [
                    'material' => $material,
                    'action' => route('materials.update', $material),
                    'method' => 'PUT',
                    'submitLabel' => $labels['submit'],
                    'cancelLabel' => $labels['cancel'],
                    'cancelUrl' => route('materials.index', $material->course),
                ])
            </div>
        </div>
    </div>
</x-app-layout>