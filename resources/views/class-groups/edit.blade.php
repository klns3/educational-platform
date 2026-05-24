<x-app-layout>
    <div class="min-h-screen bg-[#f6f8fc] text-slate-950 transition-colors duration-300 dark:bg-[#07111f] dark:text-white">
        <div class="relative overflow-hidden">

            <div class="relative mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
                <section class="mb-7">
                    <p class="mb-4 inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700 dark:border-blue-400/20 dark:bg-blue-500/10 dark:text-blue-200">
                        Группы
                    </p>

                    <h1 class="text-3xl font-black tracking-tight text-slate-950 sm:text-4xl dark:text-white">
                        Редактирование группы
                    </h1>

                    <p class="mt-3 max-w-2xl text-sm font-semibold text-slate-500 sm:text-base dark:text-slate-300">
                        Изменение группы и состава студентов.
                    </p>
                </section>

                <form method="POST"
                      action="{{ route('class-groups.update', $classGroup) }}"
                      class="overflow-hidden rounded-[1.7rem] border border-white bg-white shadow-sm shadow-slate-200/70 dark:border-white/10 dark:bg-white/[0.04] dark:shadow-none">
                    @csrf
                    @method('PUT')

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
                                   value="{{ old('name', $classGroup->name) }}"
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
                                      class="w-full resize-none rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-950 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-4 focus:ring-blue-500/10 dark:border-white/10 dark:bg-white/[0.04] dark:text-white">{{ old('description', $classGroup->description) }}</textarea>

                            @error('description')
                                <p class="mt-2 text-sm font-bold text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-3 block text-sm font-black text-slate-700 dark:text-slate-200">
                                Студенты группы
                            </label>

                            <div class="max-h-80 overflow-y-auto rounded-[1.4rem] border border-slate-200 bg-slate-50 p-4 dark:border-white/10 dark:bg-white/[0.03]">
                                <div class="grid gap-3">
                                    @forelse($students as $student)
                                        <label class="flex items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-white p-4 transition hover:border-blue-400 hover:bg-blue-50 dark:border-white/10 dark:bg-white/[0.04] dark:hover:bg-blue-500/10">
                                            <div class="min-w-0">
                                                <p class="truncate font-black text-slate-950 dark:text-white">
                                                    {{ $student->name }}
                                                </p>

                                                <p class="truncate text-sm font-semibold text-slate-500 dark:text-slate-400">
                                                    {{ $student->email }}
                                                </p>

                                                @if($student->class_group_id && $student->class_group_id !== $classGroup->id)
                                                    <p class="mt-2 inline-flex rounded-full bg-orange-50 px-3 py-1 text-xs font-black text-orange-600 dark:bg-orange-500/15 dark:text-orange-300">
                                                        Сейчас в другой группе
                                                    </p>
                                                @endif
                                            </div>

                                            <input type="checkbox"
                                                   name="students[]"
                                                   value="{{ $student->id }}"
                                                   class="h-5 w-5 shrink-0 rounded-lg border-slate-300 bg-white text-blue-600 focus:ring-blue-500 dark:border-white/10 dark:bg-slate-900"
                                                   @checked(old('students') ? in_array($student->id, old('students', [])) : $student->class_group_id === $classGroup->id)>
                                        </label>
                                    @empty
                                        <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-10 text-center dark:border-white/10 dark:bg-white/[0.04]">
                                            <p class="font-black text-slate-950 dark:text-white">
                                                Студентов пока нет
                                            </p>
                                        </div>
                                    @endforelse
                                </div>
                            </div>

                            @error('students')
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
                            Сохранить
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>