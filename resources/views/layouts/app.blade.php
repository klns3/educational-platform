<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <script>
            (function () {
                const storedTheme = localStorage.getItem('theme');
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                const theme = storedTheme === 'light' || storedTheme === 'dark'
                    ? storedTheme
                    : (prefersDark ? 'dark' : 'light');

                document.documentElement.classList.toggle('dark', theme === 'dark');
                document.documentElement.dataset.theme = theme;
            })();
        </script>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-slate-50 text-slate-900 dark:bg-[#07111f] dark:text-slate-100 transition-colors">
        <div class="min-h-screen bg-slate-50 bg-[radial-gradient(circle,rgba(148,163,184,0.18)_1.1px,transparent_1.1px),radial-gradient(circle_at_left_bottom,rgba(249,115,22,0.36),rgba(254,215,170,0.24)_24%,rgba(255,255,255,0)_60%)] bg-[length:28px_28px,auto] bg-fixed bg-[position:0_0,0_0] transition-colors dark:bg-[#07111f] dark:bg-[radial-gradient(circle,rgba(148,163,184,0.14)_1px,transparent_1px)] dark:bg-[length:32px_32px] lg:flex">
            @include('layouts.navigation')

            <div class="min-w-0 flex-1">
                <!-- Page Heading -->
                @isset($header)
                    <header class="bg-white shadow dark:bg-gray-800">
                        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @endisset

                <!-- Page Content -->
                <main>
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
