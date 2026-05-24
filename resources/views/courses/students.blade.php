<x-app-layout>
    <div class="min-h-screen bg-[#f6f8fc] text-slate-950 transition-colors duration-300 dark:bg-[#07111f] dark:text-white">
        <div class="relative overflow-hidden">

            <div class="relative mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
                <section class="mb-7 flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="mb-4 inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700 dark:border-blue-400/20 dark:bg-blue-500/10 dark:text-blue-200">
                            Курсы
                        </p>

                        <h1 class="text-3xl font-black tracking-tight text-slate-950 sm:text-4xl dark:text-white">
                            Студенты курса
                        </h1>

                        <p class="mt-3 max-w-2xl text-sm font-semibold text-slate-500 sm:text-base dark:text-slate-300">
                            {{ $course->title }}
                        </p>
                    </div>

                    <a href="{{ route('courses.index') }}"
                       class="inline-flex items-center justify-center rounded-2xl border border-slate-200 px-5 py-3 text-sm font-black text-slate-700 transition hover:border-blue-400 hover:text-blue-600 dark:border-white/10 dark:text-slate-200">
                        Назад
                    </a>
                </section>

                @if(session('success'))
                    <div class="mb-6 rounded-[1.4rem] border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-black text-emerald-700 dark:border-emerald-400/20 dark:bg-emerald-500/10 dark:text-emerald-200">
                        {{ session('success') }}
                    </div>
                @endif

                <form method="POST"
                      action="{{ route('courses.students.sync', $course) }}"
                      class="overflow-hidden rounded-[1.7rem] border border-white bg-white shadow-sm shadow-slate-200/70 dark:border-white/10 dark:bg-white/[0.04] dark:shadow-none">
                    @csrf
                    @method('PUT')

                    <div class="border-b border-slate-100 px-6 py-5 dark:border-white/10">
                        <h2 class="text-xl font-black text-slate-950 dark:text-white">
                            Назначение студентов
                        </h2>

                        <p class="mt-1 text-sm font-semibold text-slate-500 dark:text-slate-400">
                            Отметьте студентов, которые должны иметь доступ к курсу.
                        </p>
                    </div>

                    <div class="grid gap-3 p-6">
                        @forelse($students as $student)
                            <label class="flex items-center justify-between gap-4 rounded-[1.4rem] border border-slate-200 bg-slate-50 p-4 transition hover:border-blue-400 hover:bg-blue-50 dark:border-white/10 dark:bg-white/[0.03] dark:hover:bg-blue-500/10">
                                <div class="min-w-0">
                                    <p class="truncate font-black text-slate-950 dark:text-white">
                                        {{ $student->name }}
                                    </p>
                                    <p class="truncate text-sm font-semibold text-slate-500 dark:text-slate-400">
                                        {{ $student->email }}
                                    </p>
                                </div>

                                <input type="checkbox"
                                       name="students[]"
                                       value="{{ $student->id }}"
                                       class="h-5 w-5 shrink-0 rounded-lg border-slate-300 bg-white text-blue-600 focus:ring-blue-500 dark:border-white/10 dark:bg-slate-900"
                                       @checked($course->students->contains($student->id))>
                            </label>
                        @empty
                            <div class="rounded-[1.4rem] border border-dashed border-slate-300 bg-slate-50 px-6 py-10 text-center dark:border-white/10 dark:bg-white/[0.03]">
                                <p class="font-black text-slate-950 dark:text-white">
                                    Студентов пока нет
                                </p>
                            </div>
                        @endforelse
                    </div>

                    <div class="flex justify-end border-t border-slate-100 px-6 py-5 dark:border-white/10">
                        <button type="submit"
                                class="inline-flex items-center justify-center rounded-2xl bg-blue-600 px-6 py-3 text-sm font-black text-white shadow-lg shadow-blue-600/20 transition hover:-translate-y-0.5 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-400">
                            Сохранить
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>