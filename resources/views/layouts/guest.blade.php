<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'Scribe') }}</title>

        <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Crect width='24' height='24' rx='6' fill='%237c3aed'/%3E%3Cpath fill='white' d='M14 5l5 5-9 9-4 1 1-4 7-7z'/%3E%3C/svg%3E">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-gray-900">
        <div class="auth-bg min-h-screen flex flex-col justify-center items-center px-4 py-10">
            <a href="/" class="flex items-center gap-3 mb-6">
                <span class="grid place-items-center h-11 w-11 rounded-2xl bg-gradient-to-br from-violet-500 to-indigo-600 text-white shadow-lg">
                    <x-application-logo class="h-6 w-6" />
                </span>
                <span class="text-2xl font-extrabold text-white tracking-tight">{{ config('app.name', 'Scribe') }}</span>
            </a>

            <div class="w-full sm:max-w-md">
                <div class="rounded-2xl bg-white/95 dark:bg-gray-900/90 backdrop-blur-xl shadow-2xl ring-1 ring-white/20 px-7 py-8">
                    {{ $slot }}
                </div>
                <p class="text-center text-xs text-white/60 mt-6">Open-source · bring your own key or run a local model · {{ now()->year }}</p>
            </div>
        </div>
    </body>
</html>
