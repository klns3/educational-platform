<x-app-layout>
    <div class="min-h-screen bg-[#f6f8fc] text-slate-950 transition-colors duration-300 dark:bg-[#07111f] dark:text-white">
        <div class="relative overflow-hidden">
            <div class="pointer-events-none absolute inset-0 hidden dark:block">
                <div class="absolute left-[16%] top-0 h-80 w-80 rounded-full bg-blue-600/20 blur-3xl"></div>
                <div class="absolute right-0 top-10 h-96 w-96 rounded-full bg-violet-600/20 blur-3xl"></div>
                <div class="absolute bottom-0 left-1/2 h-72 w-72 rounded-full bg-cyan-500/10 blur-3xl"></div>
            </div>

            <div class="relative mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
                <section class="mb-7">
                    <p class="mb-4 inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700 dark:border-blue-400/20 dark:bg-blue-500/10 dark:text-blue-200">
                        Администрирование
                    </p>

                    <h1 class="text-3xl font-black tracking-tight text-slate-950 sm:text-4xl dark:text-white">
                        Редактирование пользователя
                    </h1>

                    <p class="mt-3 max-w-2xl text-sm font-semibold text-slate-500 sm:text-base dark:text-slate-300">
                        {{ $user->name }} — {{ $user->email }}
                    </p>
                </section>

                @if($errors->any())
                    <div class="mb-6 rounded-[1.4rem] border border-red-200 bg-red-50 px-5 py-4 text-sm font-bold text-red-700 dark:border-red-400/20 dark:bg-red-500/10 dark:text-red-200">
                        <ul class="list-inside list-disc">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST"
                      action="{{ route('users.update', $user) }}"
                      class="overflow-hidden rounded-[1.7rem] border border-white bg-white shadow-sm shadow-slate-200/70 dark:border-white/10 dark:bg-white/[0.04] dark:shadow-none">
                    @csrf
                    @method('PATCH')

                    <div class="border-b border-slate-100 px-6 py-5 dark:border-white/10">
                        <h2 class="text-xl font-black text-slate-950 dark:text-white">
                            Роль и группа
                        </h2>
                    </div>

                    <div class="grid gap-6 p-6">
                        <div>
                            <label class="mb-2 block text-sm font-black text-slate-700 dark:text-slate-200">
                                Роль
                            </label>

                            <select id="roleSelect"
                                    name="role"
                                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-950 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-4 focus:ring-blue-500/10 dark:border-white/10 dark:bg-white/[0.04] dark:text-white">
                                <option value="" @selected(old('role', $user->role) === null)>
                                    Без роли (ожидает назначения)
                                </option>

                                <option value="admin" @selected(old('role', $user->role) === 'admin')>
                                    Администратор
                                </option>

                                <option value="teacher" @selected(old('role', $user->role) === 'teacher')>
                                    Преподаватель
                                </option>

                                <option value="student" @selected(old('role', $user->role) === 'student')>
                                    Студент
                                </option>
                            </select>
                        </div>

                        <div id="groupBlock" class="rounded-[1.4rem] border border-blue-200 bg-blue-50 p-5 dark:border-blue-400/20 dark:bg-blue-500/10">
                            <label class="mb-2 block text-sm font-black text-blue-700 dark:text-blue-200">
                                Учебная группа
                            </label>

                            <select id="groupSelect"
                                    name="class_group_id"
                                    class="w-full rounded-2xl border border-blue-200 bg-white px-4 py-3 text-sm font-bold text-slate-950 outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-500/10 disabled:cursor-not-allowed disabled:opacity-60 dark:border-white/10 dark:bg-white/[0.05] dark:text-white">
                                <option value="">Без группы</option>

                                @foreach($groups as $group)
                                    <option value="{{ $group->id }}"
                                        @selected((string) old('class_group_id', $user->class_group_id) === (string) $group->id)>
                                        {{ $group->name }}
                                    </option>
                                @endforeach
                            </select>

                            <p id="groupHelpText" class="mt-2 hidden text-sm font-semibold text-blue-700/70 dark:text-blue-200/70">
                                Группу можно назначить только студенту.
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-col gap-3 border-t border-slate-100 px-6 py-5 sm:flex-row sm:items-center sm:justify-between dark:border-white/10">
                        <a href="{{ route('users.index') }}"
                           class="inline-flex items-center justify-center rounded-2xl border border-slate-200 px-5 py-3 text-sm font-black text-slate-700 transition hover:border-blue-400 hover:text-blue-600 dark:border-white/10 dark:text-slate-200">
                            Назад
                        </a>

                        <button class="inline-flex items-center justify-center rounded-2xl bg-blue-600 px-6 py-3 text-sm font-black text-white shadow-lg shadow-blue-600/20 transition hover:-translate-y-0.5 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-400">
                            Сохранить
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const roleSelect = document.getElementById('roleSelect');
        const groupSelect = document.getElementById('groupSelect');
        const groupBlock = document.getElementById('groupBlock');
        const groupHelpText = document.getElementById('groupHelpText');

        function toggleGroupSelect() {
            const isStudent = roleSelect.value === 'student';

            groupSelect.disabled = !isStudent;
            groupBlock.classList.toggle('opacity-60', !isStudent);
            groupHelpText.classList.toggle('hidden', isStudent);

            if (!isStudent) {
                groupSelect.value = '';
            }
        }

        roleSelect.addEventListener('change', toggleGroupSelect);
        toggleGroupSelect();
    </script>
</x-app-layout>