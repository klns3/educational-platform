@php
    $navLabels = [
        'system' => 'Учебная система',
        'dashboard' => 'Панель',
        'courses' => 'Курсы',
        'schedule' => 'Расписание',
        'results' => 'Результаты',
        'messages' => 'Сообщения',
        'tickets' => 'Заявки',
        'ai' => 'ИИ-аналитика',
        'notifications' => 'Уведомления',
        'logs' => 'Журнал',
        'users' => 'Пользователи',
        'groups' => 'Группы',
        'admin' => 'Администратор',
        'teacher' => 'Преподаватель',
        'student' => 'Студент',
        'profile' => 'Профиль',
        'logout' => 'Выйти',
        'theme' => 'Тема',
    ];

    $roleLabel = match (Auth::user()->role) {
        'admin' => $navLabels['admin'],
        'teacher' => $navLabels['teacher'],
        default => $navLabels['student'],
    };

    $mainNavItems = [
        ['route' => route('dashboard'), 'active' => 'dashboard*', 'label' => $navLabels['dashboard'], 'icon' => 'dashboard'],
        ['route' => route('courses.index'), 'active' => 'courses.*', 'label' => $navLabels['courses'], 'icon' => 'courses'],
        ['route' => route('schedule.index'), 'active' => 'schedule.*', 'label' => $navLabels['schedule'], 'icon' => 'schedule'],
        ['route' => route('results.my'), 'active' => 'results.*', 'label' => $navLabels['results'], 'icon' => 'results'],
        ['route' => route('messages.index'), 'active' => 'messages.*', 'label' => $navLabels['messages'], 'icon' => 'messages', 'messages' => true],
        ['route' => route('support-tickets.index'), 'active' => 'support-tickets.*', 'label' => $navLabels['tickets'], 'icon' => 'tickets'],
    ];

    if (in_array(Auth::user()->role, ['admin', 'teacher'], true)) {
        $mainNavItems[] = ['route' => route('ai-analytics.index'), 'active' => 'ai-analytics.*', 'label' => $navLabels['ai'], 'icon' => 'ai'];
    }

    $adminNavItems = [
        ['route' => route('action-logs.index'), 'active' => 'action-logs.*', 'label' => $navLabels['logs'], 'icon' => 'logs'],
        ['route' => route('users.index'), 'active' => 'users.*', 'label' => $navLabels['users'], 'icon' => 'users'],
        ['route' => route('class-groups.index'), 'active' => 'class-groups.*', 'label' => $navLabels['groups'], 'icon' => 'groups'],
    ];
@endphp

<nav x-data="{ open: false, collapsed: localStorage.getItem('navigationCollapsed') === 'true', toggleCollapsed() { this.collapsed = !this.collapsed; localStorage.setItem('navigationCollapsed', this.collapsed ? 'true' : 'false'); } }"
     :class="collapsed ? 'is-collapsed lg:w-[92px]' : 'lg:w-[280px]'"
     class="app-navigation-shell sticky top-0 z-40 border-b border-white/60 bg-white/70 backdrop-blur-2xl transition-all duration-300 dark:border-white/10 dark:bg-[#07111f] lg:h-screen lg:shrink-0 lg:border-b-0 lg:!bg-transparent lg:p-3 lg:!backdrop-blur-none">
    <div class="app-navigation-panel mx-auto max-w-7xl px-4 sm:px-6 lg:mx-0 lg:flex lg:h-full lg:max-w-none lg:flex-col lg:rounded-[1.75rem] lg:border lg:border-white/70 lg:bg-white/[0.42] lg:p-4 lg:shadow-[0_24px_70px_rgba(15,23,42,0.12),inset_0_1px_0_rgba(255,255,255,0.72)] lg:backdrop-blur-2xl lg:dark:border-white/10 lg:dark:bg-white/[0.07] lg:dark:shadow-[0_24px_70px_rgba(2,8,23,0.32),inset_0_1px_0_rgba(255,255,255,0.10)]">
        <div class="flex h-16 justify-between lg:h-full lg:flex-col lg:items-stretch">
            <div class="flex items-center gap-6 lg:flex-col lg:items-stretch lg:gap-6">
                <div class="flex items-center justify-between gap-3">
                <a href="{{ route('dashboard') }}" class="flex min-w-0 items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 font-bold text-white shadow-lg shadow-blue-600/20">
                        EP
                    </div>

                    <div class="hidden min-w-0 sm:block">
                        <p class="whitespace-nowrap text-sm font-black leading-4 text-slate-950 dark:text-white">Education Platform</p>
                        <p class="whitespace-nowrap text-xs font-semibold text-slate-500 dark:text-slate-400">{{ $navLabels['system'] }}</p>
                    </div>
                </a>

                <button type="button"
                        @click="toggleCollapsed()"
                        class="app-navigation-collapse-button hidden h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-white/60 bg-white/[0.55] text-slate-600 backdrop-blur-xl transition hover:bg-white/[0.75] hover:text-slate-950 dark:border-white/10 dark:bg-white/[0.06] dark:text-slate-300 dark:hover:bg-white/[0.10] dark:hover:text-white lg:inline-flex"
                        :title="collapsed ? 'Развернуть меню' : 'Свернуть меню'">
                    <svg x-show="!collapsed" class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m15 18-6-6 6-6"/></svg>
                    <svg x-show="collapsed" x-cloak class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 18 6-6-6-6"/></svg>
                </button>
                </div>

                <div class="hidden lg:block">
                    <p class="mb-2 px-3 text-[11px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">Основное</p>

                    <div class="grid gap-1">
                        @foreach($mainNavItems as $item)
                            @php($isActive = request()->routeIs($item['active']))

                            <a href="{{ $item['route'] }}"
                               @if($item['messages'] ?? false) data-messages-count-url="{{ route('messages.unread-count') }}" @endif
                               class="relative flex items-center gap-3 rounded-2xl px-3 py-2.5 text-sm font-bold transition {{ $isActive ? 'border border-blue-500/25 bg-white/[0.7] text-blue-700 shadow-[inset_0_1px_0_rgba(255,255,255,0.62),0_12px_28px_rgba(37,99,235,0.10)] dark:border-white/15 dark:bg-white/[0.08] dark:text-white dark:shadow-[inset_0_1px_0_rgba(255,255,255,0.08),0_12px_28px_rgba(2,8,23,0.18)]' : 'text-slate-600 hover:bg-white/[0.65] hover:text-slate-950 dark:text-slate-300 dark:hover:bg-white/[0.07] dark:hover:text-white' }}">
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl {{ $isActive ? 'bg-blue-600 text-white dark:bg-blue-500 dark:text-white' : 'bg-slate-900/5 text-slate-500 dark:bg-white/[0.06] dark:text-slate-400' }}">
                                    @switch($item['icon'])
                                        @case('dashboard')
                                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 13h7V4H4v9Zm9 7h7V4h-7v16ZM4 20h7v-5H4v5Z"/></svg>
                                            @break
                                        @case('courses')
                                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5.5A2.5 2.5 0 0 1 7.5 3H20v16H7.5A2.5 2.5 0 0 0 5 21.5v-16Zm0 0A2.5 2.5 0 0 0 2.5 3H2v16h.5A2.5 2.5 0 0 1 5 21.5"/></svg>
                                            @break
                                        @case('schedule')
                                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 3v4m10-4v4M4 9h16M6 5h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z"/></svg>
                                            @break
                                        @case('results')
                                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 19V9m7 10V5m7 14v-7M4 19h16"/></svg>
                                            @break
                                        @case('messages')
                                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 11.5a8.4 8.4 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.4 8.4 0 0 1-3.8-.9L3 21l1.9-5.7A8.4 8.4 0 0 1 4 11.5a8.5 8.5 0 1 1 17 0Z"/></svg>
                                            @break
                                        @case('ai')
                                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v3m0 12v3M5.64 5.64l2.12 2.12m8.48 8.48 2.12 2.12M3 12h3m12 0h3M5.64 18.36l2.12-2.12m8.48-8.48 2.12-2.12M12 8a4 4 0 1 1 0 8 4 4 0 0 1 0-8Z"/></svg>
                                            @break
                                        @default
                                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 18 6-6-6-6M4 6l6 6-6 6"/></svg>
                                    @endswitch
                                </span>

                                <span>{{ $item['label'] }}</span>

                                @if(($item['messages'] ?? false) && $unreadMessagesCount > 0)
                                    <span id="messagesBadge" class="absolute right-3 top-2 flex h-5 min-w-5 items-center justify-center rounded-full bg-red-600 px-1 text-xs text-white">
                                        {{ $unreadMessagesCount }}
                                    </span>
                                @endif
                            </a>
                        @endforeach
                    </div>

                    @if(Auth::user()->role === 'admin')
                        <div class="mt-5 border-t border-slate-900/10 pt-4 dark:border-white/10">
                            <p class="mb-2 px-3 text-[11px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">Управление</p>

                            <div class="grid gap-1">
                                @foreach($adminNavItems as $item)
                                    @php($isActive = request()->routeIs($item['active']))

                                    <a href="{{ $item['route'] }}"
                                       class="relative flex items-center gap-3 rounded-2xl px-3 py-2.5 text-sm font-bold transition {{ $isActive ? 'border border-blue-500/25 bg-white/[0.7] text-blue-700 shadow-[inset_0_1px_0_rgba(255,255,255,0.62),0_12px_28px_rgba(37,99,235,0.10)] dark:border-white/15 dark:bg-white/[0.08] dark:text-white dark:shadow-[inset_0_1px_0_rgba(255,255,255,0.08),0_12px_28px_rgba(2,8,23,0.18)]' : 'text-slate-600 hover:bg-white/[0.65] hover:text-slate-950 dark:text-slate-300 dark:hover:bg-white/[0.07] dark:hover:text-white' }}">
                                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl {{ $isActive ? 'bg-blue-600 text-white dark:bg-blue-500 dark:text-white' : 'bg-slate-900/5 text-slate-500 dark:bg-white/[0.06] dark:text-slate-400' }}">
                                            @switch($item['icon'])
                                                @case('users')
                                                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2m7-10a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm13 10v-2a4 4 0 0 0-3-3.87m-3-11.26a4 4 0 0 1 0 7.75"/></svg>
                                                    @break
                                                @case('groups')
                                                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5h16v14H4V5Zm4 4h8M8 13h5"/></svg>
                                                    @break
                                                @default
                                                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 2m5-2a8 8 0 1 1-16 0 8 8 0 0 1 16 0Z"/></svg>
                                            @endswitch
                                        </span>

                                        <span>{{ $item['label'] }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="hidden items-stretch gap-3 lg:mt-auto lg:grid lg:grid-cols-4">
                <a href="{{ route('notifications.index') }}"
                   data-notifications-count-url="{{ route('notifications.unread-count') }}"
                   class="relative inline-flex h-11 items-center justify-center rounded-xl border border-white/60 bg-white/[0.55] text-slate-600 backdrop-blur-xl transition-colors hover:bg-white/[0.75] hover:text-slate-950 dark:border-white/10 dark:bg-white/[0.06] dark:text-slate-300 dark:hover:bg-white/[0.10] dark:hover:text-white"
                   title="{{ $navLabels['notifications'] }}">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15 17H9m10-2V11a7 7 0 1 0-14 0v4l-2 2h18l-2-2Z"/>
                        <path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M10 21h4"/>
                    </svg>

                    @if($unreadNotificationsCount > 0)
                        <span id="notificationsBadge" class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-red-600 px-1 text-xs text-white">
                            {{ $unreadNotificationsCount }}
                        </span>
                    @endif
                </a>

                <button type="button"
                        id="themeToggle"
                        class="inline-flex h-11 items-center justify-center rounded-xl border border-white/60 bg-white/[0.55] text-slate-600 backdrop-blur-xl transition-colors hover:bg-white/[0.75] hover:text-slate-950 dark:border-white/10 dark:bg-white/[0.06] dark:text-slate-300 dark:hover:bg-white/[0.10] dark:hover:text-white"
                        title="{{ $navLabels['theme'] }}">
                    <span id="themeToggleIcon" aria-hidden="true">◐</span>
                </button>

                <a href="{{ route('profile.edit') }}"
                   class="inline-flex h-11 items-center justify-center rounded-xl border border-white/60 bg-white/[0.55] text-slate-600 backdrop-blur-xl transition-colors hover:bg-white/[0.75] hover:text-slate-950 dark:border-white/10 dark:bg-white/[0.06] dark:text-slate-300 dark:hover:bg-white/[0.10] dark:hover:text-white"
                   title="{{ $navLabels['profile'] }}">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm7.4-2a7.8 7.8 0 0 0 0-2l2-1.5-2-3.5-2.4 1a8 8 0 0 0-1.7-1L15 3h-4l-.4 3a8 8 0 0 0-1.7 1l-2.4-1-2 3.5 2 1.5a7.8 7.8 0 0 0 0 2l-2 1.5 2 3.5 2.4-1a8 8 0 0 0 1.7 1l.4 3h4l.4-3a8 8 0 0 0 1.7-1l2.4 1 2-3.5-2.1-1.5Z"/></svg>
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="inline-flex h-11 w-full items-center justify-center rounded-xl border border-white/60 bg-white/[0.55] text-slate-600 backdrop-blur-xl transition-colors hover:bg-white/[0.75] hover:text-slate-950 dark:border-white/10 dark:bg-white/[0.06] dark:text-slate-300 dark:hover:bg-white/[0.10] dark:hover:text-white"
                            title="{{ $navLabels['logout'] }}">
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4M10 17l5-5-5-5m5 5H3"/></svg>
                    </button>
                </form>

                <div class="col-span-4">
                    <a href="{{ route('profile.edit') }}" class="flex w-full items-center gap-3 rounded-2xl px-2 py-2 text-slate-700 transition hover:bg-white/[0.65] dark:text-slate-200 dark:hover:bg-white/[0.07]">
                        <div class="flex h-9 w-9 items-center justify-center overflow-hidden rounded-full bg-blue-600 font-bold text-white">
                            @if(Auth::user()->avatar_url)
                                <img src="{{ Auth::user()->avatar_url }}"
                                     class="h-full w-full object-cover"
                                     alt="avatar"
                                     loading="lazy">
                            @else
                                {{ Auth::user()->initials }}
                            @endif
                        </div>

                        <div class="min-w-0 flex-1 text-left">
                            <p class="truncate text-sm font-black leading-4 text-slate-950 dark:text-white">{{ Auth::user()->name }}</p>
                            <p class="truncate text-xs font-semibold text-slate-500 dark:text-slate-400">{{ $roleLabel }}</p>
                        </div>
                    </a>
                </div>
            </div>

            <div class="flex items-center gap-2 lg:hidden">
                <a href="{{ route('notifications.index') }}"
                   class="relative inline-flex h-10 w-10 items-center justify-center rounded-xl border border-white/60 bg-white/[0.55] text-slate-600 backdrop-blur-xl transition-colors dark:border-white/10 dark:bg-white/[0.06] dark:text-slate-300"
                   title="{{ $navLabels['notifications'] }}">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15 17H9m10-2V11a7 7 0 1 0-14 0v4l-2 2h18l-2-2Z"/>
                        <path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M10 21h4"/>
                    </svg>

                    @if($unreadNotificationsCount > 0)
                        <span class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-red-600 px-1 text-xs text-white">
                            {{ $unreadNotificationsCount }}
                        </span>
                    @endif
                </a>

                <button type="button"
                        id="themeToggleMobile"
                        class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-white/60 bg-white/[0.55] text-slate-600 backdrop-blur-xl transition-colors dark:border-white/10 dark:bg-white/[0.06] dark:text-slate-300"
                        title="{{ $navLabels['theme'] }}">
                    <span id="themeToggleIconMobile" aria-hidden="true">◐</span>
                </button>

                <button @click="open = ! open"
                        class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-white/60 bg-white/[0.55] text-slate-700 backdrop-blur-xl transition-colors dark:border-white/10 dark:bg-white/[0.06] dark:text-slate-200"
                        aria-label="Открыть меню">
                    <svg x-show="!open" class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    <svg x-show="open" x-cloak class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 6l12 12M18 6 6 18"/>
                    </svg>
                </button>
            </div>
        </div>

        <div x-show="open"
             x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="border-t border-white/50 py-3 lg:hidden dark:border-white/10">
            <div class="rounded-[1.4rem] border border-white/70 bg-white/[0.9] p-3 shadow-[0_18px_48px_rgba(15,23,42,0.12)] backdrop-blur-2xl dark:border-white/10 dark:bg-slate-950/82">
                <div class="mb-3 flex items-center gap-3 rounded-2xl px-3 py-2">
                    <div class="flex h-10 w-10 items-center justify-center overflow-hidden rounded-full bg-blue-600 font-bold text-white">
                        @if(Auth::user()->avatar_url)
                            <img src="{{ Auth::user()->avatar_url }}"
                                 class="h-full w-full object-cover"
                                 alt="avatar"
                                 loading="lazy">
                        @else
                            {{ Auth::user()->initials }}
                        @endif
                    </div>

                    <div class="min-w-0">
                        <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">{{ Auth::user()->name }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ $roleLabel }}</p>
                    </div>
                </div>

                <div class="grid gap-2">
                    <a href="{{ route('dashboard') }}" class="rounded-2xl px-4 py-3 text-sm {{ request()->routeIs('dashboard*') ? 'glass-chip text-slate-950 dark:text-white' : 'text-slate-700 hover:bg-white/70 dark:text-slate-300 dark:hover:bg-white/[0.08]' }}">{{ $navLabels['dashboard'] }}</a>
                    <a href="{{ route('courses.index') }}" class="rounded-2xl px-4 py-3 text-sm {{ request()->routeIs('courses.*') ? 'glass-chip text-slate-950 dark:text-white' : 'text-slate-700 hover:bg-white/70 dark:text-slate-300 dark:hover:bg-white/[0.08]' }}">{{ $navLabels['courses'] }}</a>
                    <a href="{{ route('schedule.index') }}" class="rounded-2xl px-4 py-3 text-sm {{ request()->routeIs('schedule.*') ? 'glass-chip text-slate-950 dark:text-white' : 'text-slate-700 hover:bg-white/70 dark:text-slate-300 dark:hover:bg-white/[0.08]' }}">{{ $navLabels['schedule'] }}</a>
                    <a href="{{ route('results.my') }}" class="rounded-2xl px-4 py-3 text-sm {{ request()->routeIs('results.*') ? 'glass-chip text-slate-950 dark:text-white' : 'text-slate-700 hover:bg-white/70 dark:text-slate-300 dark:hover:bg-white/[0.08]' }}">{{ $navLabels['results'] }}</a>
                    <a href="{{ route('messages.index') }}" class="rounded-2xl px-4 py-3 text-sm {{ request()->routeIs('messages.*') ? 'glass-chip text-slate-950 dark:text-white' : 'text-slate-700 hover:bg-white/70 dark:text-slate-300 dark:hover:bg-white/[0.08]' }}">{{ $navLabels['messages'] }}</a>
                    <a href="{{ route('support-tickets.index') }}" class="rounded-2xl px-4 py-3 text-sm {{ request()->routeIs('support-tickets.*') ? 'glass-chip text-slate-950 dark:text-white' : 'text-slate-700 hover:bg-white/70 dark:text-slate-300 dark:hover:bg-white/[0.08]' }}">{{ $navLabels['tickets'] }}</a>
                    @if(in_array(Auth::user()->role, ['admin', 'teacher'], true))
                        <a href="{{ route('ai-analytics.index') }}" class="rounded-2xl px-4 py-3 text-sm {{ request()->routeIs('ai-analytics.*') ? 'glass-chip text-slate-950 dark:text-white' : 'text-slate-700 hover:bg-white/70 dark:text-slate-300 dark:hover:bg-white/[0.08]' }}">{{ $navLabels['ai'] }}</a>
                    @endif

                    @if(Auth::user()->role === 'admin')
                        <a href="{{ route('action-logs.index') }}" class="rounded-2xl px-4 py-3 text-sm {{ request()->routeIs('action-logs.*') ? 'glass-chip text-slate-950 dark:text-white' : 'text-slate-700 hover:bg-white/70 dark:text-slate-300 dark:hover:bg-white/[0.08]' }}">{{ $navLabels['logs'] }}</a>
                        <a href="{{ route('users.index') }}" class="rounded-2xl px-4 py-3 text-sm {{ request()->routeIs('users.*') ? 'glass-chip text-slate-950 dark:text-white' : 'text-slate-700 hover:bg-white/70 dark:text-slate-300 dark:hover:bg-white/[0.08]' }}">{{ $navLabels['users'] }}</a>
                        <a href="{{ route('class-groups.index') }}" class="rounded-2xl px-4 py-3 text-sm {{ request()->routeIs('class-groups.*') ? 'glass-chip text-slate-950 dark:text-white' : 'text-slate-700 hover:bg-white/70 dark:text-slate-300 dark:hover:bg-white/[0.08]' }}">{{ $navLabels['groups'] }}</a>
                    @endif

                    <a href="{{ route('profile.edit') }}" class="rounded-2xl px-4 py-3 text-sm text-slate-700 hover:bg-white/70 dark:text-slate-300 dark:hover:bg-white/[0.08]">{{ $navLabels['profile'] }}</a>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full rounded-2xl px-4 py-3 text-left text-sm text-slate-700 hover:bg-white/70 dark:text-slate-300 dark:hover:bg-white/[0.08]">
                            {{ $navLabels['logout'] }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</nav>

<script>
    (function () {
        const messagesLink = document.querySelector('[data-messages-count-url]');
        const notificationsLink = document.querySelector('[data-notifications-count-url]');
        const themeToggle = document.getElementById('themeToggle');
        const themeToggleIcon = document.getElementById('themeToggleIcon');
        const themeToggleMobile = document.getElementById('themeToggleMobile');
        const themeToggleIconMobile = document.getElementById('themeToggleIconMobile');

        function updateThemeToggle() {
            const isDark = document.documentElement.classList.contains('dark');
            const svgIcon = isDark
                ? '<svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4V2m0 20v-2m8-8h2M2 12h2m14.36-6.36 1.42-1.42M4.22 19.78l1.42-1.42m12.72 0 1.42 1.42M4.22 4.22l1.42 1.42M12 16a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z"/></svg>'
                : '<svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 14.7A8.5 8.5 0 0 1 9.3 3 7 7 0 1 0 21 14.7Z"/></svg>';

            if (themeToggleIcon) {
                themeToggleIcon.innerHTML = svgIcon;
            }

            if (themeToggleIconMobile) {
                themeToggleIconMobile.innerHTML = svgIcon;
            }

            return;
            const icon = isDark ? '☀' : '☾';
            const title = isDark ? 'Светлая тема' : 'Тёмная тема';

            if (themeToggle && themeToggleIcon) {
                themeToggleIcon.textContent = icon;
                themeToggle.title = title;
            }

            if (themeToggleMobile && themeToggleIconMobile) {
                themeToggleIconMobile.textContent = icon;
                themeToggleMobile.title = title;
            }
        }

        function renderBadge({ count, badgeId, container }) {
            let badge = document.getElementById(badgeId);

            if (count <= 0) {
                badge?.remove();
                return;
            }

            if (!badge) {
                badge = document.createElement('span');
                badge.id = badgeId;
                badge.className = 'absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-red-600 px-1 text-xs text-white';
                container.appendChild(badge);
            }

            badge.textContent = count;
        }

        async function refreshBadge({ url, badgeId, container }) {
            try {
                const response = await fetch(url, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    }
                });

                if (!response.ok) {
                    return;
                }

                const data = await response.json();

                renderBadge({
                    count: Number(data.count) || 0,
                    badgeId,
                    container,
                });
            } catch (error) {
                console.error(error);
            }
        }

        function refreshAllBadges() {
            if (messagesLink) {
                refreshBadge({
                    url: messagesLink.dataset.messagesCountUrl,
                    badgeId: 'messagesBadge',
                    container: messagesLink,
                });
            }

            if (notificationsLink) {
                refreshBadge({
                    url: notificationsLink.dataset.notificationsCountUrl,
                    badgeId: 'notificationsBadge',
                    container: notificationsLink,
                });
            }
        }

        function toggleTheme() {
            const nextTheme = document.documentElement.classList.contains('dark') ? 'light' : 'dark';

            localStorage.setItem('theme', nextTheme);
            document.documentElement.classList.toggle('dark', nextTheme === 'dark');
            document.documentElement.dataset.theme = nextTheme;

            updateThemeToggle();
        }

        updateThemeToggle();

        if (themeToggle) {
            themeToggle.addEventListener('click', toggleTheme);
        }

        if (themeToggleMobile) {
            themeToggleMobile.addEventListener('click', toggleTheme);
        }

        refreshAllBadges();
        setInterval(refreshAllBadges, 5000);
    })();
</script>
