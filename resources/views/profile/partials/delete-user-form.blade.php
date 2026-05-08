<section class="space-y-6">
    <header>
        <h2 class="text-xl font-black text-slate-950 dark:text-white">
            Удаление аккаунта
        </h2>

        <p class="mt-1 text-sm font-semibold text-slate-500 dark:text-slate-400">
            После удаления аккаунта все данные будут безвозвратно стерты. Сохраните важную информацию заранее.
        </p>
    </header>

    <button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
        class="inline-flex items-center justify-center rounded-2xl border border-red-200 bg-red-50 px-5 py-3 text-sm font-black text-red-600 transition hover:border-red-400 hover:bg-red-100 dark:border-red-400/20 dark:bg-red-500/10 dark:text-red-300 dark:hover:bg-red-500/15">
        Удалить аккаунт
    </button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-black text-slate-950 dark:text-white">
                Подтвердите удаление аккаунта
            </h2>

            <p class="mt-2 text-sm font-semibold text-slate-500 dark:text-slate-400">
                Это действие нельзя отменить. Введите пароль, чтобы подтвердить удаление.
            </p>

            <div class="mt-6">
                <x-input-label for="password" value="Пароль" class="sr-only" />

                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1 block w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold dark:border-white/10 dark:bg-white/[0.04]"
                    placeholder="Введите пароль"
                />

                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button type="button"
                        x-on:click="$dispatch('close')"
                        class="inline-flex items-center justify-center rounded-2xl border border-slate-200 px-5 py-3 text-sm font-black text-slate-700 transition hover:border-blue-400 hover:text-blue-600 dark:border-white/10 dark:text-slate-200">
                    Отмена
                </button>

                <button type="submit"
                        class="inline-flex items-center justify-center rounded-2xl bg-red-600 px-6 py-3 text-sm font-black text-white shadow-lg shadow-red-600/20 transition hover:-translate-y-0.5 hover:bg-red-700 dark:bg-red-500 dark:hover:bg-red-400">
                    Удалить
                </button>
            </div>
        </form>
    </x-modal>
</section>