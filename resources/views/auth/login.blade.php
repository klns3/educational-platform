<x-guest-layout>
    <div class="min-h-screen bg-[#f6f8fc] text-slate-950 transition-colors duration-300 dark:bg-[#07111f] dark:text-white">
        <div class="relative flex min-h-screen items-center justify-center overflow-hidden px-4 py-10">

            <div class="relative grid w-full max-w-5xl overflow-hidden rounded-[2rem] border border-white bg-white shadow-xl shadow-slate-200/70 dark:border-white/10 dark:bg-white/[0.04] dark:shadow-none lg:grid-cols-[1fr_420px]">
                <div class="hidden bg-blue-600 p-10 text-white dark:bg-blue-500/20 lg:flex lg:flex-col lg:justify-between">
                    <div>
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white/15 text-2xl font-black">
                            EP
                        </div>

                        <h1 class="mt-8 text-4xl font-black leading-tight">
                            Образовательная платформа
                        </h1>

                        <p class="mt-4 text-sm font-semibold text-blue-50 dark:text-slate-200">
                            Курсы, тесты, материалы, расписание и общение в одной системе.
                        </p>
                    </div>

                    <div class="grid gap-3">
                        <div class="rounded-2xl bg-white/15 p-4">
                            <p class="text-sm font-black">Все в одном месте</p>
                            <p class="mt-1 text-xs font-semibold text-blue-50 dark:text-slate-300">
                                Для студентов, преподавателей и администраторов.
                            </p>
                        </div>

                        <div class="rounded-2xl bg-white/15 p-4">
                            <p class="text-sm font-black">Личный кабинет</p>
                            <p class="mt-1 text-xs font-semibold text-blue-50 dark:text-slate-300">
                                После входа система сама откроет нужную панель.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="p-6 sm:p-8 lg:p-10">
                    <p class="mb-4 inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700 dark:border-blue-400/20 dark:bg-blue-500/10 dark:text-blue-200">
                        Вход в систему
                    </p>

                    <h2 class="text-3xl font-black tracking-tight text-slate-950 dark:text-white">
                        Добро пожаловать
                    </h2>

                    <p class="mt-2 text-sm font-semibold text-slate-500 dark:text-slate-300">
                        Введите данные аккаунта, чтобы продолжить работу.
                    </p>

                    <x-auth-session-status class="mt-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-700 dark:border-emerald-400/20 dark:bg-emerald-500/10 dark:text-emerald-300" :status="session('status')" />

                    <form method="POST" action="{{ route('login') }}" class="mt-7">
                        @csrf

                        <div>
                            <label for="email" class="text-sm font-black text-slate-700 dark:text-slate-200">
                                Email
                            </label>

                            <input
                                id="email"
                                class="mt-2 block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-950 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-white/10 dark:bg-white/[0.04] dark:text-white dark:placeholder:text-slate-500"
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                required
                                autofocus
                                autocomplete="username"
                                placeholder="name@example.com"
                            >

                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        <div class="mt-5">
                            <label for="password" class="text-sm font-black text-slate-700 dark:text-slate-200">
                                Пароль
                            </label>

                            <input
                                id="password"
                                class="mt-2 block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-950 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-white/10 dark:bg-white/[0.04] dark:text-white dark:placeholder:text-slate-500"
                                type="password"
                                name="password"
                                required
                                autocomplete="current-password"
                                placeholder="Введите пароль"
                            >

                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        <div class="mt-5 flex items-center justify-between gap-4">
                            <label for="remember_me" class="inline-flex items-center">
                                <input
                                    id="remember_me"
                                    type="checkbox"
                                    class="h-5 w-5 appearance-none rounded-md border border-slate-300 bg-white shadow-sm transition checked:border-blue-600 checked:bg-blue-600 checked:bg-[url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0_0_20_20' fill='none'%3E%3Cpath d='M5_10.5l3.2_3.2L15_7' stroke='white' stroke-width='2.4' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E\")] checked:bg-[length:0.8rem_0.8rem] checked:bg-center checked:bg-no-repeat focus:ring-2 focus:ring-blue-500/30 dark:border-white/12 dark:bg-white/[0.03] dark:checked:border-cyan-400 dark:checked:bg-cyan-400"
                                    name="remember"
                                >

                                <span class="ms-2 text-sm font-bold text-slate-500 dark:text-slate-400">
                                    Запомнить меня
                                </span>
                            </label>

                            @if (Route::has('password.request'))
                                <a
                                    class="text-sm font-black text-blue-600 transition hover:text-blue-700 dark:text-blue-300 dark:hover:text-blue-200"
                                    href="{{ route('password.request') }}"
                                >
                                    Забыли пароль?
                                </a>
                            @endif
                        </div>

                        <button
                            type="submit"
                            class="mt-7 inline-flex w-full items-center justify-center rounded-2xl bg-blue-600 px-5 py-4 text-sm font-black text-white shadow-lg shadow-blue-600/20 transition hover:-translate-y-0.5 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-400"
                        >
                            Войти
                        </button>

                        <div class="mt-6 rounded-2xl bg-slate-50 p-4 text-center dark:bg-white/[0.04]">
                            <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">
                                Нет аккаунта?
                                <a href="{{ route('register') }}" class="font-black text-blue-600 hover:text-blue-700 dark:text-blue-300">
                                    Зарегистрироваться
                                </a>
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
