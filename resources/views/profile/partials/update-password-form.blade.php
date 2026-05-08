<section>
    <header>
        <h2 class="text-xl font-black text-slate-950 dark:text-white">
            Обновление пароля
        </h2>

        <p class="mt-1 text-sm font-semibold text-slate-500 dark:text-slate-400">
            Используйте надёжный пароль для защиты аккаунта.
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 grid gap-5">
        @csrf
        @method('put')

        <div>
            <x-input-label for="update_password_current_password" value="Текущий пароль" />
            <x-text-input id="update_password_current_password" name="current_password" type="password" class="mt-2 block w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold dark:border-white/10 dark:bg-white/[0.04]" autocomplete="current-password" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password" value="Новый пароль" />
            <x-text-input id="update_password_password" name="password" type="password" class="mt-2 block w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold dark:border-white/10 dark:bg-white/[0.04]" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password_confirmation" value="Подтверждение пароля" />
            <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" class="mt-2 block w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold dark:border-white/10 dark:bg-white/[0.04]" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4">
            <button type="submit"
                    class="inline-flex items-center justify-center rounded-2xl bg-blue-600 px-6 py-3 text-sm font-black text-white shadow-lg shadow-blue-600/20 transition hover:-translate-y-0.5 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-400">
                Сохранить
            </button>

            @if (session('status') === 'password-updated')
                <p x-data="{ show: true }"
                   x-show="show"
                   x-transition
                   x-init="setTimeout(() => show = false, 2000)"
                   class="text-sm font-bold text-emerald-600 dark:text-emerald-300">
                    Сохранено.
                </p>
            @endif
        </div>
    </form>
</section>