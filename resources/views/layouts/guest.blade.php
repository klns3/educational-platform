<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      x-data="{
        darkMode: localStorage.getItem('theme') === 'dark',
        toggleTheme() {
            this.darkMode = !this.darkMode;
            localStorage.setItem('theme', this.darkMode ? 'dark' : 'light');
        }
      }"
      x-bind:class="{ 'dark': darkMode }">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Education Platform') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased bg-white dark:bg-[#07111f] transition-colors duration-300">

    <!-- КНОПКА ТЕМЫ -->
    <div class="fixed z-50" style="top: 32px; right: 32px;">
    <button
        @click="toggleTheme()"
        type="button"
        class="flex items-center gap-2 rounded-2xl border border-white/20 bg-white/70 px-4 py-2 text-sm font-bold text-slate-700 shadow-lg backdrop-blur-xl transition hover:-translate-y-0.5 hover:border-blue-300 hover:text-blue-600 dark:border-white/10 dark:bg-white/[0.05] dark:text-slate-200 dark:hover:border-blue-400/40"
    >
        <span x-show="!darkMode">🌙</span>
        <span x-show="darkMode">☀️</span>
        <span x-text="darkMode ? 'Светлая тема' : 'Тёмная тема'"></span>
    </button>
    </div>

    {{ $slot }}

</body>
</html>
