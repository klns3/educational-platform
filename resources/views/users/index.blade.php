<x-app-layout>
    <div class="min-h-screen bg-[#f6f8fc] text-slate-950 transition-colors duration-300 dark:bg-[#07111f] dark:text-white">
        <div class="relative overflow-hidden">

            <div class="relative mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
                <section class="mb-7 flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="mb-4 inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700 dark:border-blue-400/20 dark:bg-blue-500/10 dark:text-blue-200">
                            Администрирование
                        </p>

                        <h1 class="text-3xl font-black tracking-tight text-slate-950 sm:text-4xl dark:text-white">
                            Пользователи
                        </h1>

                        <p class="mt-3 max-w-2xl text-sm font-semibold text-slate-500 sm:text-base dark:text-slate-300">
                            Управление ролями и учебными группами.
                        </p>
                    </div>

                    <a href="{{ route('invitation-codes.index') }}"
                       class="inline-flex items-center justify-center rounded-2xl bg-blue-600 px-6 py-3 text-sm font-black text-white shadow-lg shadow-blue-600/20 transition hover:-translate-y-0.5 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-400">
                        Пригласительные коды
                    </a>
                </section>

                @if(session('success'))
                    <div class="mb-6 rounded-[1.4rem] border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-black text-emerald-700 dark:border-emerald-400/20 dark:bg-emerald-500/10 dark:text-emerald-200">
                        {{ session('success') }}
                    </div>
                @endif

                @if($pendingUsersCount > 0)
                    <section class="mb-6 overflow-hidden rounded-[1.7rem] border border-amber-200 bg-amber-50 shadow-sm shadow-amber-100 dark:border-amber-400/20 dark:bg-amber-500/10 dark:shadow-none">
                        <div class="flex flex-col gap-4 p-6 md:flex-row md:items-center md:justify-between">
                            <div>
                                <p class="text-sm font-black text-amber-700 dark:text-amber-200">
                                    Требуется действие
                                </p>

                                <h2 class="mt-2 text-2xl font-black text-amber-950 dark:text-white">
                                    Есть пользователи без роли
                                </h2>

                                <p class="mt-2 text-sm font-semibold text-amber-800 dark:text-amber-200">
                                    {{ $pendingUsersCount }} пользователь(ей) ожидают назначения роли.
                                </p>
                            </div>

                            <a href="{{ route('users.index', ['role' => 'pending']) }}"
                               class="inline-flex items-center justify-center rounded-2xl bg-amber-500 px-5 py-3 text-sm font-black text-white shadow-lg shadow-amber-500/20 transition hover:-translate-y-0.5 hover:bg-amber-600">
                                Показать ожидающих
                            </a>
                        </div>
                    </section>
                @endif

                <section class="mb-6 overflow-hidden rounded-[1.7rem] border border-white bg-white shadow-sm shadow-slate-200/70 dark:border-white/10 dark:bg-white/[0.04] dark:shadow-none">
                    <div class="border-b border-slate-100 px-6 py-5 dark:border-white/10">
                        <h2 class="text-xl font-black text-slate-950 dark:text-white">
                            Фильтры
                        </h2>
                    </div>

                    <form method="GET" action="{{ route('users.index') }}" class="grid grid-cols-1 gap-4 p-6 md:grid-cols-4">
                        <div>
                            <label class="mb-2 block text-sm font-black text-slate-700 dark:text-slate-200">
                                Поиск
                            </label>

                            <input type="text"
                                   name="search"
                                   value="{{ request('search') }}"
                                   placeholder="Имя или email"
                                   class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-950 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-4 focus:ring-blue-500/10 dark:border-white/10 dark:bg-white/[0.04] dark:text-white">
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-black text-slate-700 dark:text-slate-200">
                                Роль
                            </label>

                            <select name="role"
                                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-950 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-4 focus:ring-blue-500/10 dark:border-white/10 dark:bg-white/[0.04] dark:text-white">
                                <option value="">Все</option>
                                <option value="pending" @selected(request('role') === 'pending')>Ожидает назначения</option>
                                <option value="admin" @selected(request('role') === 'admin')>Администратор</option>
                                <option value="teacher" @selected(request('role') === 'teacher')>Преподаватель</option>
                                <option value="student" @selected(request('role') === 'student')>Студент</option>
                            </select>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-black text-slate-700 dark:text-slate-200">
                                Группа
                            </label>

                            <select name="class_group_id"
                                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-950 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-4 focus:ring-blue-500/10 dark:border-white/10 dark:bg-white/[0.04] dark:text-white">
                                <option value="">Все</option>

                                @foreach($groups as $group)
                                    <option value="{{ $group->id }}" @selected((string) request('class_group_id') === (string) $group->id)>
                                        {{ $group->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex flex-col gap-3 md:items-end md:justify-end sm:flex-row">
                            <a href="{{ route('users.index') }}"
                               class="inline-flex items-center justify-center rounded-2xl border border-slate-200 px-5 py-3 text-sm font-black text-slate-700 transition hover:border-blue-400 hover:text-blue-600 dark:border-white/10 dark:text-slate-200">
                                Сбросить
                            </a>

                            <button class="inline-flex items-center justify-center rounded-2xl bg-blue-600 px-6 py-3 text-sm font-black text-white shadow-lg shadow-blue-600/20 transition hover:-translate-y-0.5 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-400">
                                Найти
                            </button>
                        </div>
                    </form>
                </section>

                <section class="overflow-hidden rounded-[1.7rem] border border-white bg-white shadow-sm shadow-slate-200/70 dark:border-white/10 dark:bg-white/[0.04] dark:shadow-none">
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[900px] text-sm">
                            <thead class="border-b border-slate-100 bg-slate-50 text-slate-500 dark:border-white/10 dark:bg-white/[0.03] dark:text-slate-400">
                                <tr>
                                    <th class="px-5 py-4 text-left font-black">Пользователь</th>
                                    <th class="px-5 py-4 text-left font-black">Роль</th>
                                    <th class="px-5 py-4 text-left font-black">Группа</th>
                                    <th class="px-5 py-4 text-left font-black">Дата регистрации</th>
                                    <th class="px-5 py-4 text-right font-black">Действия</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-slate-100 dark:divide-white/10">
                                @forelse($users as $user)
                                    <tr class="transition hover:bg-slate-50 dark:hover:bg-white/[0.03] {{ $user->role === null ? 'bg-amber-50/70 dark:bg-amber-500/10' : '' }}">
                                        <td class="px-5 py-4">
                                            <p class="font-black text-slate-950 dark:text-white">
                                                {{ $user->name }}
                                            </p>

                                            <p class="mt-1 text-xs font-semibold text-slate-500 dark:text-slate-400">
                                                {{ $user->email }}
                                            </p>

                                            @if($user->role === null)
                                                <span class="mt-2 inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-black text-amber-700 dark:bg-amber-500/15 dark:text-amber-300">
                                                    Ожидает назначения роли
                                                </span>
                                            @endif
                                        </td>

                                        <td class="px-5 py-4">
                                            @if($user->role === null)
                                                <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-black text-amber-700 dark:bg-amber-500/15 dark:text-amber-300">
                                                    Не назначена
                                                </span>
                                            @else
                                                <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-black text-blue-600 dark:bg-blue-500/15 dark:text-blue-300">
                                                    {{ $user->role }}
                                                </span>
                                            @endif
                                        </td>

                                        <td class="px-5 py-4 font-semibold text-slate-600 dark:text-slate-300">
                                            {{ $user->classGroup?->name ?? 'Без группы' }}
                                        </td>

                                        <td class="px-5 py-4 font-semibold text-slate-500 dark:text-slate-400">
                                            {{ $user->created_at->format('d.m.Y H:i') }}
                                        </td>

                                        <td class="px-5 py-4 text-right">
                                            <a href="{{ route('users.edit', $user) }}"
                                               class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-black text-white transition hover:-translate-y-0.5
                                               {{ $user->role === null
                                                    ? 'bg-amber-500 shadow-md shadow-amber-500/20 hover:bg-amber-600'
                                                    : 'bg-blue-600 shadow-md shadow-blue-600/20 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-400'
                                               }}">
                                                {{ $user->role === null ? 'Назначить роль' : 'Редактировать' }}
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-5 py-12 text-center">
                                            <p class="text-lg font-black text-slate-950 dark:text-white">
                                                Пользователи не найдены
                                            </p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>

                <div class="mt-6">
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>