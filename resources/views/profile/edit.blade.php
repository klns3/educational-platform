<x-app-layout>
    <div class="min-h-screen bg-[#f6f8fc] text-slate-950 transition-colors duration-300 dark:bg-[#07111f] dark:text-white">
        <div class="relative overflow-hidden">

            <div class="relative max-w-7xl mx-auto px-4 py-10 sm:px-6 lg:px-8">

                <div class="mb-10">
                    <h1 class="text-3xl sm:text-4xl font-black tracking-tight text-slate-950 dark:text-white">
                        Профиль пользователя
                    </h1>
                    <p class="text-slate-500 dark:text-slate-300 mt-2 font-semibold text-sm sm:text-base">
                        Управление аккаунтом
                    </p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    <!-- Левая карточка -->
                    <div class="rounded-[1.6rem] border border-white bg-white shadow-sm p-6 text-center
                                dark:border-white/10 dark:bg-white/[0.04]">

                        <div class="h-24 w-24 mx-auto rounded-[1.4rem] overflow-hidden
                                    bg-blue-600 flex items-center justify-center text-white text-3xl font-black mb-4 shadow">

                            @if(Auth::user()->avatar_url)
                                <img src="{{ Auth::user()->avatar_url }}"
                                     class="h-full w-full object-cover"
                                     alt="avatar"
                                     loading="lazy">
                            @else
                                {{ Auth::user()->initials }}
                            @endif
                        </div>

                        <h2 class="text-xl font-black text-slate-900 dark:text-white">
                            {{ Auth::user()->name }}
                        </h2>

                        <p class="text-slate-500 dark:text-slate-400 mt-1 text-sm font-semibold">
                            {{ Auth::user()->email }}
                        </p>

                        <div class="mt-4 inline-flex items-center rounded-full px-3 py-1 text-xs font-bold
                                    bg-blue-50 text-blue-700
                                    dark:bg-blue-500/10 dark:text-blue-300">
                            {{ Auth::user()->role }}
                        </div>
                    </div>

                    <!-- Правая часть -->
                    <div class="lg:col-span-2 space-y-6">

                        <div class="rounded-[1.6rem] border border-white bg-white shadow-sm p-6
                                    dark:border-white/10 dark:bg-white/[0.04]">
                            @include('profile.partials.update-profile-information-form')
                        </div>

                        <div class="rounded-[1.6rem] border border-white bg-white shadow-sm p-6
                                    dark:border-white/10 dark:bg-white/[0.04]">
                            @include('profile.partials.update-password-form')
                        </div>

                    </div>

                </div>

            </div>
        </div>
    </div>
</x-app-layout>