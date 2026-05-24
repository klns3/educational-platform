<x-app-layout>
    <div class="min-h-screen bg-[#f6f8fc] text-slate-950 transition-colors duration-300 dark:bg-[#07111f] dark:text-white">
        <div class="relative overflow-hidden">

            <div class="relative mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
                <section class="mb-7">
                    <p class="mb-4 inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700 dark:border-blue-400/20 dark:bg-blue-500/10 dark:text-blue-200">
                        Администрирование
                    </p>

                    <h1 class="text-3xl font-black tracking-tight text-slate-950 sm:text-4xl dark:text-white">
                        Журнал действий
                    </h1>

                    <p class="mt-3 max-w-2xl text-sm font-semibold text-slate-500 sm:text-base dark:text-slate-300">
                        История важных действий пользователей системы.
                    </p>
                </section>

                <section class="mb-6 overflow-hidden rounded-[1.7rem] border border-white bg-white shadow-sm shadow-slate-200/70 dark:border-white/10 dark:bg-white/[0.04] dark:shadow-none">
                    <div class="border-b border-slate-100 px-6 py-5 dark:border-white/10">
                        <h2 class="text-xl font-black text-slate-950 dark:text-white">
                            Фильтры
                        </h2>
                    </div>

                    <form method="GET" action="{{ route('action-logs.index') }}" class="grid grid-cols-1 gap-4 p-6 md:grid-cols-5">
                        <div class="md:col-span-2">
                            <label class="mb-2 block text-sm font-black text-slate-700 dark:text-slate-200">
                                Поиск
                            </label>

                            <input type="text"
                                   name="search"
                                   value="{{ request('search') }}"
                                   placeholder="Пользователь, действие, описание, IP"
                                   class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-950 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-4 focus:ring-blue-500/10 dark:border-white/10 dark:bg-white/[0.04] dark:text-white">
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-black text-slate-700 dark:text-slate-200">
                                Действие
                            </label>

                            <select name="action"
                                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-950 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-4 focus:ring-blue-500/10 dark:border-white/10 dark:bg-white/[0.04] dark:text-white">
                                <option value="">Все</option>
                                @foreach($actions as $action)
                                    <option value="{{ $action }}" @selected(request('action') === $action)>
                                        {{ $action }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-black text-slate-700 dark:text-slate-200">
                                Дата от
                            </label>

                            <input type="date"
                                   name="date_from"
                                   value="{{ request('date_from') }}"
                                   class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-950 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-4 focus:ring-blue-500/10 dark:border-white/10 dark:bg-white/[0.04] dark:text-white">
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-black text-slate-700 dark:text-slate-200">
                                Дата до
                            </label>

                            <input type="date"
                                   name="date_to"
                                   value="{{ request('date_to') }}"
                                   class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-950 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-4 focus:ring-blue-500/10 dark:border-white/10 dark:bg-white/[0.04] dark:text-white">
                        </div>

                        <div class="flex flex-col gap-3 md:col-span-5 sm:flex-row sm:justify-end">
                            <a href="{{ route('action-logs.index') }}"
                               class="inline-flex items-center justify-center rounded-2xl border border-slate-200 px-5 py-3 text-sm font-black text-slate-700 transition hover:border-blue-400 hover:text-blue-600 dark:border-white/10 dark:text-slate-200">
                                Сбросить
                            </a>

                            <button type="submit"
                                    class="inline-flex items-center justify-center rounded-2xl bg-blue-600 px-6 py-3 text-sm font-black text-white shadow-lg shadow-blue-600/20 transition hover:-translate-y-0.5 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-400">
                                Найти
                            </button>
                        </div>
                    </form>
                </section>

                <section class="overflow-hidden rounded-[1.7rem] border border-white bg-white shadow-sm shadow-slate-200/70 dark:border-white/10 dark:bg-white/[0.04] dark:shadow-none">
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[980px] text-sm">
                            <thead class="border-b border-slate-100 bg-slate-50 text-slate-500 dark:border-white/10 dark:bg-white/[0.03] dark:text-slate-400">
                                <tr>
                                    <th class="px-5 py-4 text-left font-black">Дата</th>
                                    <th class="px-5 py-4 text-left font-black">Пользователь</th>
                                    <th class="px-5 py-4 text-left font-black">Действие</th>
                                    <th class="px-5 py-4 text-left font-black">Описание</th>
                                    <th class="px-5 py-4 text-left font-black">IP</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-slate-100 dark:divide-white/10">
                                @forelse($logs as $log)
                                    <tr class="transition hover:bg-slate-50 dark:hover:bg-white/[0.03]">
                                        <td class="whitespace-nowrap px-5 py-4 font-semibold text-slate-500 dark:text-slate-400">
                                            {{ $log->created_at->format('d.m.Y H:i') }}
                                        </td>

                                        <td class="px-5 py-4">
                                            <p class="font-black text-slate-950 dark:text-white">
                                                {{ $log->user?->name ?? 'Система' }}
                                            </p>

                                            @if($log->user?->email)
                                                <p class="mt-1 text-xs font-semibold text-slate-500 dark:text-slate-400">
                                                    {{ $log->user->email }}
                                                </p>
                                            @endif
                                        </td>

                                        <td class="px-5 py-4">
                                            <span class="whitespace-nowrap rounded-full bg-blue-50 px-3 py-1 text-xs font-black text-blue-600 dark:bg-blue-500/15 dark:text-blue-300">
                                                {{ $log->action }}
                                            </span>
                                        </td>

                                        <td class="px-5 py-4 font-semibold text-slate-600 dark:text-slate-300">
                                            {{ $log->description }}
                                        </td>

                                        <td class="whitespace-nowrap px-5 py-4 font-semibold text-slate-500 dark:text-slate-400">
                                            {{ $log->ip_address ?? '—' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-5 py-12 text-center">
                                            <p class="text-lg font-black text-slate-950 dark:text-white">
                                                Записей по выбранным фильтрам нет
                                            </p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>

                <div class="mt-6">
                    {{ $logs->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>