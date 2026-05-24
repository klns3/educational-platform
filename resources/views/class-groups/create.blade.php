<x-app-layout>
    <div class="min-h-screen bg-[#f6f8fc] text-slate-950 transition-colors duration-300 dark:bg-[#07111f] dark:text-white">
        <div class="relative overflow-hidden">

            <div class="relative mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
                <section class="mb-7">
                    <p class="mb-4 inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700 dark:border-blue-400/20 dark:bg-blue-500/10 dark:text-blue-200">
                        Группы
                    </p>

                    <h1 class="text-3xl font-black tracking-tight text-slate-950 sm:text-4xl dark:text-white">
                        Создание группы
                    </h1>

                    <p class="mt-3 max-w-2xl text-sm font-semibold text-slate-500 sm:text-base dark:text-slate-300">
                        Добавьте новую учебную группу.
                    </p>
                </section>

                <form method="POST"
                      action="{{ route('class-groups.store') }}"
                      class="overflow-hidden rounded-[1.7rem] border border-white bg-white shadow-sm shadow-slate-200/70 dark:border-white/10 dark:bg-white/[0.04] dark:shadow-none">
                    @csrf

                    <div class="border-b border-slate-100 px-6 py-5 dark:border-white/10">
                        <h2 class="text-xl font-black text-slate-950 dark:text-white">
                            Основная информация
                        </h2>
                    </div>

                    <div class="grid gap-6 p-6">
                        <div>
                            <label class="mb-2 block text-sm font-black text-slate-700 dark:text-slate-200">
                                Название группы
                            </label>

                            <input type="text"
                                   name="name"
                                   value="{{ old('name') }}"
                                   class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-950 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-4 focus:ring-blue-500/10 dark:border-white/10 dark:bg-white/[0.04] dark:text-white"
                                   required>

                            @error('name')
                                <p class="mt-2 text-sm font-bold text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-black text-slate-700 dark:text-slate-200">
                                Описание
                            </label>

                            <textarea name="description"
                                      rows="5"
                                      class="w-full resize-none rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-950 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-4 focus:ring-blue-500/10 dark:border-white/10 dark:bg-white/[0.04] dark:text-white">{{ old('description') }}</textarea>

                            @error('description')
                                <p class="mt-2 text-sm font-bold text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex flex-col gap-3 border-t border-slate-100 px-6 py-5 sm:flex-row sm:items-center sm:justify-between dark:border-white/10">
                        <a href="{{ route('class-groups.index') }}"
                           class="inline-flex items-center justify-center rounded-2xl border border-slate-200 px-5 py-3 text-sm font-black text-slate-700 transition hover:border-blue-400 hover:text-blue-600 dark:border-white/10 dark:text-slate-200">
                            Назад
                        </a>

                        <button class="inline-flex items-center justify-center rounded-2xl bg-blue-600 px-6 py-3 text-sm font-black text-white shadow-lg shadow-blue-600/20 transition hover:-translate-y-0.5 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-400">
                            Создать
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>