<x-app-layout>
    <div class="min-h-screen bg-[#f6f8fc] text-slate-950 transition-colors duration-300 dark:bg-[#07111f] dark:text-white">
        <div class="relative overflow-hidden">

            <div class="relative mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
                <section class="mb-7">
                    <p class="mb-4 inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700 dark:border-blue-400/20 dark:bg-blue-500/10 dark:text-blue-200">
                        Уведомления
                    </p>

                    <h1 class="text-3xl font-black tracking-tight text-slate-950 sm:text-4xl dark:text-white">
                        Рассылка уведомлений
                    </h1>

                    <p class="mt-3 max-w-2xl text-sm font-semibold text-slate-500 sm:text-base dark:text-slate-300">
                        Отправка уведомлений пользователям системы.
                    </p>
                </section>

                @if(session('success'))
                    <div class="mb-6 rounded-[1.4rem] border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-black text-emerald-700 dark:border-emerald-400/20 dark:bg-emerald-500/10 dark:text-emerald-200">
                        {{ session('success') }}
                    </div>
                @endif

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
                      action="{{ route('notifications.broadcast.store') }}"
                      class="overflow-hidden rounded-[1.7rem] border border-white bg-white shadow-sm shadow-slate-200/70 dark:border-white/10 dark:bg-white/[0.04] dark:shadow-none">
                    @csrf

                    <div class="border-b border-slate-100 px-6 py-5 dark:border-white/10">
                        <h2 class="text-xl font-black text-slate-950 dark:text-white">
                            Новое уведомление
                        </h2>

                        <p class="mt-1 text-sm font-semibold text-slate-500 dark:text-slate-400">
                            Выберите получателей, заголовок и текст сообщения.
                        </p>
                    </div>

                    <div class="grid gap-6 p-6">
                        <div>
                            <label class="mb-2 block text-sm font-black text-slate-700 dark:text-slate-200">
                                Кому отправить
                            </label>

                            <select name="target_type"
                                    id="target_type"
                                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-950 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-4 focus:ring-blue-500/10 dark:border-white/10 dark:bg-white/[0.04] dark:text-white">
                                @if($user->role === 'admin')
                                    <option value="all">Всем пользователям</option>
                                    <option value="students">Всем студентам</option>
                                    <option value="teachers">Всем преподавателям</option>
                                    <option value="admins">Всем администраторам</option>
                                    <option value="group">Студентам конкретной группы</option>
                                    <option value="user">Конкретному пользователю</option>
                                @else
                                    <option value="students">Всем студентам</option>
                                    <option value="group">Студентам конкретной группы</option>
                                    <option value="user">Конкретному студенту</option>
                                @endif
                            </select>
                        </div>

                        <div id="group_block" class="hidden rounded-[1.4rem] border border-blue-200 bg-blue-50 p-5 dark:border-blue-400/20 dark:bg-blue-500/10">
                            <label class="mb-2 block text-sm font-black text-blue-700 dark:text-blue-200">
                                Группа
                            </label>

                            <select name="class_group_id"
                                    class="w-full rounded-2xl border border-blue-200 bg-white px-4 py-3 text-sm font-bold text-slate-950 outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-500/10 dark:border-white/10 dark:bg-white/[0.05] dark:text-white">
                                <option value="">Выберите группу</option>

                                @foreach($groups as $group)
                                    <option value="{{ $group->id }}">
                                        {{ $group->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div id="user_block" class="hidden rounded-[1.4rem] border border-violet-200 bg-violet-50 p-5 dark:border-violet-400/20 dark:bg-violet-500/10">
                            <label class="mb-2 block text-sm font-black text-violet-700 dark:text-violet-200">
                                Пользователь
                            </label>

                            <select name="user_id"
                                    class="w-full rounded-2xl border border-violet-200 bg-white px-4 py-3 text-sm font-bold text-slate-950 outline-none transition focus:border-violet-400 focus:ring-4 focus:ring-violet-500/10 dark:border-white/10 dark:bg-white/[0.05] dark:text-white">
                                <option value="">Выберите пользователя</option>

                                @foreach($students as $student)
                                    <option value="{{ $student->id }}">
                                        {{ $student->name }} — {{ $student->email }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-black text-slate-700 dark:text-slate-200">
                                Заголовок
                            </label>

                            <input type="text"
                                   name="title"
                                   value="{{ old('title') }}"
                                   class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-950 outline-none transition placeholder:text-slate-400 focus:border-blue-400 focus:bg-white focus:ring-4 focus:ring-blue-500/10 dark:border-white/10 dark:bg-white/[0.04] dark:text-white"
                                   placeholder="Например: Важное объявление"
                                   required>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-black text-slate-700 dark:text-slate-200">
                                Текст уведомления
                            </label>

                            <textarea name="body"
                                      rows="6"
                                      class="w-full resize-none rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-950 outline-none transition placeholder:text-slate-400 focus:border-blue-400 focus:bg-white focus:ring-4 focus:ring-blue-500/10 dark:border-white/10 dark:bg-white/[0.04] dark:text-white"
                                      placeholder="Введите текст уведомления"
                                      required>{{ old('body') }}</textarea>
                        </div>
                    </div>

                    <div class="flex flex-col gap-3 border-t border-slate-100 px-6 py-5 sm:flex-row sm:items-center sm:justify-between dark:border-white/10">
                        <a href="{{ route('notifications.index') }}"
                           class="inline-flex items-center justify-center rounded-2xl border border-slate-200 px-5 py-3 text-sm font-black text-slate-700 transition hover:border-blue-400 hover:text-blue-600 dark:border-white/10 dark:text-slate-200">
                            Назад
                        </a>

                        <button type="submit"
                                class="inline-flex items-center justify-center rounded-2xl bg-blue-600 px-6 py-3 text-sm font-black text-white shadow-lg shadow-blue-600/20 transition hover:-translate-y-0.5 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-400">
                            Отправить
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const targetSelect = document.getElementById('target_type');
        const groupBlock = document.getElementById('group_block');
        const userBlock = document.getElementById('user_block');

        function toggleBlocks() {
            groupBlock.classList.add('hidden');
            userBlock.classList.add('hidden');

            if (targetSelect.value === 'group') {
                groupBlock.classList.remove('hidden');
            }

            if (targetSelect.value === 'user') {
                userBlock.classList.remove('hidden');
            }
        }

        targetSelect.addEventListener('change', toggleBlocks);
        toggleBlocks();
    </script>
</x-app-layout>