<x-app-layout>
    <div class="min-h-screen bg-[#f6f8fc] text-slate-950 transition-colors duration-300 dark:bg-[#07111f] dark:text-white">
        <div class="relative overflow-hidden">

            <div class="relative mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
                <section class="mb-7 flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="mb-4 inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700 dark:border-blue-400/20 dark:bg-blue-500/10 dark:text-blue-200">
                            Группы
                        </p>

                        <h1 class="text-3xl font-black tracking-tight text-slate-950 sm:text-4xl dark:text-white">
                            Учебные группы
                        </h1>

                        <p class="mt-3 max-w-2xl text-sm font-semibold text-slate-500 sm:text-base dark:text-slate-300">
                            Создание групп и распределение студентов.
                        </p>
                    </div>

                    <a href="{{ route('class-groups.create') }}"
                       class="inline-flex items-center justify-center rounded-2xl bg-blue-600 px-5 py-3 text-sm font-black text-white shadow-lg shadow-blue-600/20 transition hover:-translate-y-0.5 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-400">
                        Создать группу
                    </a>
                </section>

                @if(session('success'))
                    <div class="mb-6 rounded-[1.4rem] border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-black text-emerald-700 dark:border-emerald-400/20 dark:bg-emerald-500/10 dark:text-emerald-200">
                        {{ session('success') }}
                    </div>
                @endif

                <section class="overflow-hidden rounded-[1.7rem] border border-white bg-white shadow-sm shadow-slate-200/70 dark:border-white/10 dark:bg-white/[0.04] dark:shadow-none">
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[760px] text-sm">
                            <thead class="border-b border-slate-100 bg-slate-50 text-slate-500 dark:border-white/10 dark:bg-white/[0.03] dark:text-slate-400">
                                <tr>
                                    <th class="px-5 py-4 text-left font-black">Название</th>
                                    <th class="px-5 py-4 text-left font-black">Описание</th>
                                    <th class="px-5 py-4 text-left font-black">Студентов</th>
                                    <th class="px-5 py-4 text-right font-black">Действия</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-slate-100 dark:divide-white/10">
                                @forelse($groups as $group)
                                    <tr class="transition hover:bg-slate-50 dark:hover:bg-white/[0.03]">
                                        <td class="px-5 py-4">
                                            <p class="font-black text-slate-950 dark:text-white">
                                                {{ $group->name }}
                                            </p>
                                        </td>

                                        <td class="px-5 py-4 font-semibold text-slate-500 dark:text-slate-400">
                                            {{ $group->description ?? '—' }}
                                        </td>

                                        <td class="px-5 py-4">
                                            <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-black text-blue-600 dark:bg-blue-500/15 dark:text-blue-300">
                                                {{ $group->students_count }}
                                            </span>
                                        </td>

                                        <td class="px-5 py-4 text-right">
                                            <div class="flex justify-end gap-2">
                                                <a href="{{ route('class-groups.edit', $group) }}"
                                                   class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-3 py-2 text-xs font-black text-slate-700 transition hover:border-blue-400 hover:text-blue-600 dark:border-white/10 dark:text-slate-200">
                                                    Редактировать
                                                </a>

                                                <form action="{{ route('class-groups.destroy', $group) }}"
                                                      method="POST"
                                                      onsubmit="return confirm('Удалить группу? Студенты останутся без группы.')">
                                                    @csrf
                                                    @method('DELETE')

                                                    <button class="inline-flex items-center justify-center rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs font-black text-red-600 transition hover:border-red-400 hover:bg-red-100 dark:border-red-400/20 dark:bg-red-500/10 dark:text-red-300 dark:hover:bg-red-500/15">
                                                        Удалить
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-5 py-12 text-center">
                                            <p class="text-lg font-black text-slate-950 dark:text-white">
                                                Группы ещё не созданы
                                            </p>

                                            <p class="mt-2 text-sm font-semibold text-slate-500 dark:text-slate-400">
                                                Создайте первую группу и распределите студентов.
                                            </p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>

                <div class="mt-8">
                    <a href="{{ route('users.index') }}"
                       class="inline-flex items-center justify-center rounded-2xl border border-slate-200 px-5 py-3 text-sm font-black text-slate-700 transition hover:border-blue-400 hover:text-blue-600 dark:border-white/10 dark:text-slate-200">
                        ← Назад к пользователям
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>