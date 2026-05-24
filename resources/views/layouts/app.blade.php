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
        <div id="aiAnalyticsLoadingOverlay" class="hidden">
            <div class="ai-analytics-loading-card">
                <div class="ai-analytics-loading-icon">
                    <div class="ai-analytics-loading-spinner"></div>
                </div>

                <p class="ai-analytics-loading-title">ИИ-аналитика загружается</p>
                <p class="ai-analytics-loading-text">Система считает показатели и запускает ML-модель. Обычно это занимает несколько секунд.</p>
            </div>
        </div>

        <div class="relative min-h-screen bg-slate-50 bg-[radial-gradient(circle,rgba(148,163,184,0.18)_1.1px,transparent_1.1px),radial-gradient(circle_at_left_bottom,rgba(249,115,22,0.36),rgba(254,215,170,0.24)_24%,rgba(255,255,255,0)_60%)] bg-[length:28px_28px,auto] bg-fixed bg-[position:0_0,0_0] transition-colors dark:bg-[#07111f] dark:bg-[radial-gradient(circle,rgba(148,163,184,0.14)_1px,transparent_1px),radial-gradient(circle_at_left_bottom,rgba(186,230,253,0.13),rgba(125,211,252,0.07)_28%,rgba(7,17,31,0)_64%)] dark:bg-[length:32px_32px,auto] lg:flex">
            @include('layouts.navigation')

            <div class="relative z-10 min-w-0 flex-1">
                <div id="educationBackground" class="education-background" aria-hidden="true">
                    <svg class="education-bg-icon education-bg-book" data-depth="10" style="--x: 69%; --y: 10%; --size: 68px; --rotate: -10deg;" viewBox="0 0 64 64">
                        <path d="M12 14h17a7 7 0 0 1 7 7v29a7 7 0 0 0-7-7H12V14Z"/>
                        <path d="M52 14H35a7 7 0 0 0-7 7v29a7 7 0 0 1 7-7h17V14Z"/>
                        <path d="M28 21v29"/>
                    </svg>

                    <svg class="education-bg-icon education-bg-pencil" data-depth="16" style="--x: 8%; --y: 30%; --size: 56px; --rotate: 42deg;" viewBox="0 0 64 64">
                        <path d="m12 45 3 7 7-3 28-28-10-10-28 28Z"/>
                        <path d="m36 15 10 10"/>
                        <path d="m12 45 10 4"/>
                    </svg>

                    <svg class="education-bg-icon education-bg-cap" data-depth="8" style="--x: 19%; --y: 78%; --size: 74px; --rotate: 0deg;" viewBox="0 0 64 64">
                        <path d="m6 24 26-12 26 12-26 12L6 24Z"/>
                        <path d="M18 31v11c8 7 20 7 28 0V31"/>
                        <path d="M52 27v16"/>
                    </svg>

                    <svg class="education-bg-icon education-bg-light" data-depth="13" style="--x: 88%; --y: 34%; --size: 58px; --rotate: 0deg;" viewBox="0 0 64 64">
                        <path d="M42 29a10 10 0 1 0-20 0c0 6 5 8 6 14h8c1-6 6-8 6-14Z"/>
                        <path d="M28 50h8"/>
                        <path d="M29 56h6"/>
                        <path d="M32 6v6M12 29H6m52 0h-6M17 14l4 4m26-4-4 4"/>
                    </svg>

                    <svg class="education-bg-icon education-bg-ruler" data-depth="18" style="--x: 90%; --y: 76%; --size: 64px; --rotate: 45deg;" viewBox="0 0 64 64">
                        <path d="M10 43 43 10l11 11-33 33L10 43Z"/>
                        <path d="m22 37 4 4m3-11 4 4m3-11 4 4m3-11 4 4"/>
                    </svg>

                    <svg class="education-bg-icon education-bg-calculator" data-depth="7" style="--x: 5%; --y: 60%; --size: 62px; --rotate: 0deg;" viewBox="0 0 64 64">
                        <path d="M14 10h36v44H14V10Z"/>
                        <path d="M22 18h20v8H22z"/>
                        <path d="M22 35h4m10 0h4m10 0h4M22 45h4m10 0h4m10 0h4"/>
                    </svg>

                    <svg class="education-bg-icon education-bg-formula" data-depth="11" style="--x: 10%; --y: 12%; --size: 76px; --rotate: -8deg;" viewBox="0 0 100 48">
                        <path d="M10 30c9-14 13-14 22 0m0-18v30m16-25h22M59 6v22m0 0c0 9-5 13-13 13"/>
                        <path d="M78 18c5-8 8-8 13 0m0-10v20"/>
                    </svg>

                    <svg class="education-bg-icon education-bg-plane" data-depth="20" style="--x: 83%; --y: 18%; --size: 72px; --rotate: 18deg;" viewBox="0 0 64 64">
                        <path d="M6 29 56 8 42 56 31 36 6 29Z"/>
                        <path d="m31 36 25-28"/>
                    </svg>

                    <svg class="education-bg-icon education-bg-book-small" data-depth="9" style="--x: 42%; --y: 82%; --size: 50px; --rotate: 2deg;" viewBox="0 0 64 64">
                        <path d="M12 14h17a7 7 0 0 1 7 7v29a7 7 0 0 0-7-7H12V14Z"/>
                        <path d="M52 14H35a7 7 0 0 0-7 7v29a7 7 0 0 1 7-7h17V14Z"/>
                    </svg>

                    <svg class="education-bg-icon education-bg-graph" data-depth="14" style="--x: 55%; --y: 28%; --size: 54px; --rotate: 0deg;" viewBox="0 0 64 64">
                        <path d="M10 52h44"/>
                        <path d="M18 44V28"/>
                        <path d="M32 44V18"/>
                        <path d="M46 44V34"/>
                    </svg>

                    <svg class="education-bg-icon education-bg-checklist" data-depth="12" style="--x: 31%; --y: 18%; --size: 48px; --rotate: -4deg;" viewBox="0 0 64 64">
                        <path d="M18 10h28v44H18V10Z"/>
                        <path d="m24 24 3 3 6-7"/>
                        <path d="M38 25h6"/>
                        <path d="m24 40 3 3 6-7"/>
                        <path d="M38 41h6"/>
                    </svg>

                    <svg class="education-bg-icon education-bg-atom" data-depth="17" style="--x: 74%; --y: 66%; --size: 58px; --rotate: 12deg;" viewBox="0 0 64 64">
                        <path d="M32 36a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z"/>
                        <path d="M12 32c8-12 32-12 40 0-8 12-32 12-40 0Z"/>
                        <path d="M22 14c14 2 26 22 20 36-14-2-26-22-20-36Z"/>
                        <path d="M42 14c-14 2-26 22-20 36 14-2 26-22 20-36Z"/>
                    </svg>

                    <svg class="education-bg-icon education-bg-backpack" data-depth="6" style="--x: 50%; --y: 9%; --size: 44px; --rotate: 0deg;" viewBox="0 0 64 64">
                        <path d="M22 22v-4a10 10 0 0 1 20 0v4"/>
                        <path d="M18 22h28a6 6 0 0 1 6 6v24H12V28a6 6 0 0 1 6-6Z"/>
                        <path d="M22 40h20"/>
                        <path d="M22 52V40h20v12"/>
                    </svg>
                </div>

                <!-- Page Heading -->
                @isset($header)
                    <header class="bg-white shadow dark:bg-gray-800">
                        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @endisset

                <!-- Page Content -->
                <main class="relative z-10">
                    {{ $slot }}
                </main>
            </div>
        </div>

        <script>
            (function () {
                const overlay = document.getElementById('aiAnalyticsLoadingOverlay');

                if (!overlay) {
                    return;
                }

                function showAiAnalyticsLoader() {
                    overlay.classList.remove('hidden');
                    overlay.classList.add('flex');
                }

                document.querySelectorAll('[data-ai-analytics-loader]').forEach((link) => {
                    link.addEventListener('click', function (event) {
                        if (event.defaultPrevented || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || link.target === '_blank') {
                            return;
                        }

                        if (link.href === window.location.href) {
                            return;
                        }

                        showAiAnalyticsLoader();
                    });
                });
            })();
        </script>

        <script>
            (function () {
                const background = document.getElementById('educationBackground');

                if (!background || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                    return;
                }

                let pointerX = 0;
                let pointerY = 0;
                let frame = null;

                function render() {
                    background.style.setProperty('--mouse-x', pointerX.toFixed(3));
                    background.style.setProperty('--mouse-y', pointerY.toFixed(3));
                    frame = null;
                }

                window.addEventListener('mousemove', function (event) {
                    pointerX = (event.clientX / window.innerWidth - 0.5) * 2;
                    pointerY = (event.clientY / window.innerHeight - 0.5) * 2;

                    if (frame === null) {
                        frame = window.requestAnimationFrame(render);
                    }
                }, { passive: true });
            })();
        </script>
    </body>
</html>
