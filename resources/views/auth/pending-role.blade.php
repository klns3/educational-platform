<x-app-layout>
    <div class="min-h-screen bg-[#f6f8fc] text-slate-950 transition-colors duration-300 dark:bg-[#07111f] dark:text-white">
        <div class="relative flex min-h-screen items-center justify-center overflow-hidden px-4 py-10">

            <div class="relative w-full max-w-xl overflow-hidden rounded-[1.7rem] border border-white bg-white p-8 text-center shadow-sm shadow-slate-200/70 dark:border-white/10 dark:bg-white/[0.04] dark:shadow-none">
                <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-2xl bg-blue-50 text-3xl dark:bg-blue-500/15">
                    ⏳
                </div>

                <p class="mb-4 inline-flex items-center rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700 dark:border-amber-400/20 dark:bg-amber-500/10 dark:text-amber-200">
                    Аккаунт на проверке
                </p>

                <h1 class="text-3xl font-black tracking-tight text-slate-950 dark:text-white">
                    Ожидайте назначения роли
                </h1>

                <p class="mx-auto mt-4 max-w-md text-sm font-semibold leading-7 text-slate-500 dark:text-slate-300">
                    Регистрация успешно завершена. Администратор должен проверить ваш аккаунт и назначить роль в системе.
                </p>

                <form method="POST" action="{{ route('logout') }}" class="mt-7">
                    @csrf

                    <button class="inline-flex items-center justify-center rounded-2xl bg-blue-600 px-6 py-3 text-sm font-black text-white shadow-lg shadow-blue-600/20 transition hover:-translate-y-0.5 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-400">
                        Выйти
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>