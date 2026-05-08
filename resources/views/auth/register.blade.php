<x-guest-layout>
    <div class="min-h-screen bg-[#f6f8fc] text-slate-950 transition-colors duration-300 dark:bg-[#07111f] dark:text-white">
        <div class="relative flex min-h-screen items-center justify-center overflow-hidden px-4 py-10">
            <div class="pointer-events-none absolute inset-0 hidden dark:block">
                <div class="absolute left-[15%] top-0 h-80 w-80 rounded-full bg-blue-600/20 blur-3xl"></div>
                <div class="absolute right-[10%] top-24 h-96 w-96 rounded-full bg-violet-600/20 blur-3xl"></div>
                <div class="absolute bottom-0 left-1/2 h-72 w-72 rounded-full bg-cyan-500/10 blur-3xl"></div>
            </div>

            <div class="relative w-full max-w-md rounded-[2rem] border border-white bg-white p-8 shadow-xl shadow-slate-200/70 dark:border-white/10 dark:bg-white/[0.04] dark:shadow-none">
                <p class="mb-4 inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700 dark:border-blue-400/20 dark:bg-blue-500/10 dark:text-blue-200">
                    Регистрация
                </p>

                <h2 class="text-3xl font-black tracking-tight text-slate-950 dark:text-white">
                    Создать аккаунт
                </h2>

                <p class="mt-2 text-sm font-semibold text-slate-500 dark:text-slate-300">
                    Заполните данные, чтобы начать пользоваться системой.
                </p>

                <form method="POST" action="{{ route('register') }}" class="mt-7">
                    @csrf

                    <!-- Имя -->
                    <div>
                        <label class="text-sm font-black text-slate-700 dark:text-slate-200">
                            Имя
                        </label>

                        <input
                            type="text"
                            name="name"
                            value="{{ old('name') }}"
                            required
                            autofocus
                            autocomplete="name"
                            class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-950 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-white/10 dark:bg-white/[0.04] dark:text-white"
                            placeholder="Ваше имя"
                        >

                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <!-- Email -->
                    <div class="mt-5">
                        <label class="text-sm font-black text-slate-700 dark:text-slate-200">
                            Email
                        </label>

                        <input
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autocomplete="username"
                            class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-950 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-white/10 dark:bg-white/[0.04] dark:text-white"
                            placeholder="name@example.com"
                        >

                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <!-- Invite code -->
                    <div class="mt-5">
                        <label class="text-sm font-black text-slate-700 dark:text-slate-200">
                            Код приглашения (если есть)
                        </label>

                        <input
                            type="text"
                            name="invite_code"
                            value="{{ old('invite_code') }}"
                            autocomplete="off"
                            class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-950 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-white/10 dark:bg-white/[0.04] dark:text-white"
                            placeholder="Необязательно"
                        >

                        <x-input-error :messages="$errors->get('invite_code')" class="mt-2" />
                    </div>

                    <!-- Пароль -->
                    <div class="mt-5">
                        <label class="text-sm font-black text-slate-700 dark:text-slate-200">
                            Пароль
                        </label>

                        <input
                            type="password"
                            name="password"
                            required
                            autocomplete="new-password"
                            class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-950 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-white/10 dark:bg-white/[0.04] dark:text-white"
                            placeholder="Введите пароль"
                        >

                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <!-- Повтор пароля -->
                    <div class="mt-5">
                        <label class="text-sm font-black text-slate-700 dark:text-slate-200">
                            Подтвердите пароль
                        </label>

                        <input
                            type="password"
                            name="password_confirmation"
                            required
                            autocomplete="new-password"
                            class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-950 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-white/10 dark:bg-white/[0.04] dark:text-white"
                            placeholder="Повторите пароль"
                        >

                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                    </div>

                    <button
                        type="submit"
                        class="mt-7 w-full rounded-2xl bg-blue-600 px-5 py-4 text-sm font-black text-white shadow-lg shadow-blue-600/20 transition hover:-translate-y-0.5 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-400"
                    >
                        Зарегистрироваться
                    </button>

                    <div class="mt-6 rounded-2xl bg-slate-50 p-4 text-center dark:bg-white/[0.04]">
                        <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">
                            Уже есть аккаунт?
                            <a href="{{ route('login') }}" class="font-black text-blue-600 hover:text-blue-700 dark:text-blue-300">
                                Войти
                            </a>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>