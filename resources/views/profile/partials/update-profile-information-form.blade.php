<section>
    <header>
        <h2 class="text-xl font-black text-slate-950 dark:text-white">
            Профиль
        </h2>

        <p class="mt-1 text-sm font-semibold text-slate-500 dark:text-slate-400">
            Обновите имя, email и аватар.
        </p>
    </header>

    <form method="post"
          action="{{ route('profile.update') }}"
          enctype="multipart/form-data"
          class="mt-6 grid gap-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label value="Аватар" />

            <div class="mt-3 flex items-center gap-5">
                <div class="h-20 w-20 rounded-2xl overflow-hidden bg-blue-600 flex items-center justify-center text-2xl font-black text-white shadow">
                    @if($user->avatar_url)
                        <img src="{{ $user->avatar_url }}"
                             class="h-full w-full object-cover"
                             alt="avatar"
                             loading="lazy">
                    @else
                        {{ $user->initials }}
                    @endif
                </div>

                <input type="file"
                       name="avatar"
                       accept="image/jpeg,image/png,image/webp"
                       class="block text-sm font-semibold text-slate-600 file:mr-4 file:rounded-xl file:border-0 file:bg-blue-600 file:px-4 file:py-2 file:text-white hover:file:bg-blue-700 dark:text-slate-300 dark:file:bg-blue-500 dark:hover:file:bg-blue-400">
            </div>

            <p class="mt-2 text-xs font-semibold text-slate-500 dark:text-slate-400">
                JPG, PNG, WEBP. До 2 МБ.
            </p>

            <x-input-error class="mt-2" :messages="$errors->get('avatar')" />
        </div>

        <div>
            <x-input-label for="name" value="Имя" />
            <x-text-input id="name"
                          name="name"
                          type="text"
                          class="mt-2 block w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold dark:border-white/10 dark:bg-white/[0.04]"
                          :value="old('name', $user->name)"
                          required />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email"
                          name="email"
                          type="email"
                          class="mt-2 block w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold dark:border-white/10 dark:bg-white/[0.04]"
                          :value="old('email', $user->email)"
                          required />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />
        </div>

        <div class="flex items-center gap-4">
            <button type="submit"
                    class="inline-flex items-center justify-center rounded-2xl bg-blue-600 px-6 py-3 text-sm font-black text-white shadow-lg shadow-blue-600/20 transition hover:-translate-y-0.5 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-400">
                Сохранить
            </button>

            @if (session('status') === 'profile-updated')
                <p x-data="{ show: true }"
                   x-show="show"
                   x-transition
                   x-init="setTimeout(() => show = false, 2000)"
                   class="text-sm font-bold text-emerald-600 dark:text-emerald-300">
                    Сохранено
                </p>
            @endif
        </div>
    </form>
</section>