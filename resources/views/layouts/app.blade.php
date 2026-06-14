<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'Scribe') }}</title>

        <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Crect width='24' height='24' rx='6' fill='%237c3aed'/%3E%3Cpath fill='white' d='M14 5l5 5-9 9-4 1 1-4 7-7z'/%3E%3C/svg%3E">

        <script>
            if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            }
        </script>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased h-full text-gray-900 dark:text-gray-100">
        <div class="app-bg min-h-full flex flex-col">
            @include('layouts.navigation')

            @if (config('content.demo_mode'))
                <div class="bg-gradient-to-r from-violet-500 to-fuchsia-500 text-white text-sm text-center py-2 px-4 font-medium">
                    ✨ Demo mode - AI runs on a built-in mock model (no API key needed). Add your own key or a local model to go live.
                </div>
            @endif

            @isset($header)
                <header class="border-b border-gray-200/70 dark:border-white/5">
                    <div class="max-w-7xl mx-auto py-5 px-4 sm:px-6 lg:px-8">{{ $header }}</div>
                </header>
            @endisset

            <main class="flex-1">{{ $slot }}</main>

            <footer class="border-t border-gray-200/70 dark:border-white/5 mt-10">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex flex-col sm:flex-row items-center justify-between gap-2 text-sm text-gray-500 dark:text-gray-400">
                    <p>{{ config('app.name') }} - open-source AI writing assistant for SEO.</p>
                    <a href="https://github.com" class="hover:text-violet-600 dark:hover:text-violet-400 font-medium">View source on GitHub →</a>
                </div>
            </footer>
        </div>

        <x-flash />
    </body>
</html>
