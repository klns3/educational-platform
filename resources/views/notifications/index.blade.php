<x-app-layout>
    <div class="min-h-screen bg-[#f6f8fc] text-slate-950 transition-colors duration-300 dark:bg-[#07111f] dark:text-white">
        <div class="relative overflow-hidden">
            <div class="pointer-events-none absolute inset-0 hidden dark:block">
                <div class="absolute left-[16%] top-0 h-80 w-80 rounded-full bg-blue-600/20 blur-3xl"></div>
                <div class="absolute right-0 top-10 h-96 w-96 rounded-full bg-violet-600/20 blur-3xl"></div>
                <div class="absolute bottom-0 left-1/2 h-72 w-72 rounded-full bg-cyan-500/10 blur-3xl"></div>
            </div>

            <div class="relative mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
                <section class="mb-7 flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="mb-4 inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700 dark:border-blue-400/20 dark:bg-blue-500/10 dark:text-blue-200">
                            Уведомления
                        </p>

                        <h1 class="text-3xl font-black tracking-tight text-slate-950 sm:text-4xl dark:text-white">
                            Уведомления
                        </h1>

                        <p class="mt-3 max-w-2xl text-sm font-semibold text-slate-500 sm:text-base dark:text-slate-300">
                            События системы и новые сообщения.
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        @if(auth()->user()->role === 'admin' || auth()->user()->role === 'teacher')
                            <a href="{{ route('notifications.broadcast.create') }}"
                               class="inline-flex items-center justify-center rounded-2xl bg-blue-600 px-5 py-3 text-sm font-black text-white shadow-lg shadow-blue-600/20 transition hover:-translate-y-0.5 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-400">
                                Создать уведомление
                            </a>
                        @endif

                        @if($notifications->where('is_read', false)->count() > 0)
                            <form method="POST" action="{{ route('notifications.readAll') }}">
                                @csrf
                                @method('PATCH')

                                <button class="inline-flex items-center justify-center rounded-2xl border border-slate-200 px-5 py-3 text-sm font-black text-slate-700 transition hover:border-blue-400 hover:text-blue-600 dark:border-white/10 dark:text-slate-200">
                                    Прочитать все
                                </button>
                            </form>
                        @endif
                    </div>
                </section>

                @if(session('success'))
                    <div class="mb-6 rounded-[1.4rem] border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-black text-emerald-700 dark:border-emerald-400/20 dark:bg-emerald-500/10 dark:text-emerald-200">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="grid gap-4">
                    @forelse($notifications as $notification)
                        <article class="rounded-[1.7rem] border p-5 shadow-sm transition hover:-translate-y-0.5
                            {{ $notification->is_read
                                ? 'border-white bg-white shadow-slate-200/70 dark:border-white/10 dark:bg-white/[0.04] dark:shadow-none'
                                : 'border-blue-200 bg-blue-50 shadow-blue-100 dark:border-blue-400/20 dark:bg-blue-500/10 dark:shadow-none'
                            }}">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                <div class="min-w-0">
                                    <div class="mb-3 flex flex-wrap items-center gap-2">
                                        <span class="rounded-full px-3 py-1 text-xs font-black
                                            {{ $notification->is_read
                                                ? 'bg-slate-100 text-slate-500 dark:bg-white/10 dark:text-slate-300'
                                                : 'bg-blue-600 text-white dark:bg-blue-500'
                                            }}">
                                            {{ $notification->is_read ? 'Прочитано' : 'Новое' }}
                                        </span>

                                        <span class="text-xs font-bold text-slate-400 dark:text-slate-500">
                                            {{ $notification->created_at->format('d.m.Y H:i') }}
                                        </span>
                                    </div>

                                    <h2 class="text-xl font-black text-slate-950 dark:text-white">
                                        {{ $notification->title }}
                                    </h2>

                                    <p class="mt-2 text-sm font-semibold leading-7 text-slate-600 dark:text-slate-300">
                                        {{ $notification->body }}
                                    </p>
                                </div>

                                <div class="flex shrink-0 flex-wrap gap-2 lg:justify-end">
                                    @if($notification->action_url)
                                        <a href="{{ $notification->action_url }}"
                                           class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-black text-white transition hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-400">
                                            Открыть
                                        </a>
                                    @endif

                                    @if(!$notification->is_read)
                                        <form method="POST" action="{{ route('notifications.read', $notification) }}">
                                            @csrf
                                            @method('PATCH')

                                            <button class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-black text-slate-700 transition hover:border-blue-400 hover:text-blue-600 dark:border-white/10 dark:text-slate-200">
                                                Прочитано
                                            </button>
                                        </form>
                                    @endif

                                    <form method="POST" action="{{ route('notifications.destroy', $notification) }}">
                                        @csrf
                                        @method('DELETE')

                                        <button class="inline-flex items-center justify-center rounded-xl border border-red-200 bg-red-50 px-4 py-2.5 text-sm font-black text-red-600 transition hover:border-red-400 hover:bg-red-100 dark:border-red-400/20 dark:bg-red-500/10 dark:text-red-300 dark:hover:bg-red-500/15">
                                            Удалить
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="rounded-[1.7rem] border border-white bg-white p-10 text-center shadow-sm shadow-slate-200/70 dark:border-white/10 dark:bg-white/[0.04] dark:shadow-none">
                            <div class="mx-auto mb-5 flex h-20 w-20 items-center justify-center rounded-[1.7rem] bg-blue-50 text-4xl dark:bg-blue-500/15">
                                🔔
                            </div>

                            <h2 class="text-2xl font-black text-slate-950 dark:text-white">
                                Уведомлений пока нет
                            </h2>

                            <p class="mt-2 text-sm font-semibold text-slate-500 dark:text-slate-400">
                                Тишина. Даже сервер не шепчет.
                            </p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>