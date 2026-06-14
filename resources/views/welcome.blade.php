<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Scribe') }} - AI SEO writing assistant</title>
        <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Crect width='24' height='24' rx='6' fill='%237c3aed'/%3E%3Cpath fill='white' d='M14 5l5 5-9 9-4 1 1-4 7-7z'/%3E%3C/svg%3E">
        <script>if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) document.documentElement.classList.add('dark');</script>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased h-full bg-white dark:bg-gray-950 text-gray-900 dark:text-gray-100">
        <div class="app-bg min-h-full">
            <header class="max-w-6xl mx-auto px-6 py-6 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <span class="grid place-items-center h-9 w-9 rounded-xl bg-gradient-to-br from-violet-600 to-indigo-600 text-white shadow-sm"><x-application-logo class="h-5 w-5" /></span>
                    <span class="font-extrabold tracking-tight">{{ config('app.name', 'Scribe') }}</span>
                </div>
                <nav class="flex items-center gap-3 text-sm">
                    @auth
                        <a href="{{ route('dashboard') }}" class="font-semibold hover:underline">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="font-medium hover:underline">Log in</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="btn-primary !py-2">Get started free</a>
                        @endif
                    @endauth
                </nav>
            </header>

            <main class="max-w-6xl mx-auto px-6">
                <section class="py-20 sm:py-28 text-center">
                    <span class="chip bg-violet-100 text-violet-700 dark:bg-violet-500/15 dark:text-violet-300 mb-5">✨ Open-source · bring your own key or run a local model</span>
                    <h1 class="text-4xl sm:text-6xl font-extrabold tracking-tight leading-[1.05]">
                        Write content that <span class="gradient-text">ranks</span>,<br class="hidden sm:block"> with AI + a live SEO score.
                    </h1>
                    <p class="mt-6 text-lg text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
                        Turn a keyword into a draft, then watch your SEO score update as you edit - keyword usage,
                        headings, readability and meta, with concrete fixes. No API key needed to try it.
                    </p>
                    <div class="mt-9 flex items-center justify-center gap-3">
                        <a href="{{ route('register') }}" class="btn-primary !px-6 !py-3 text-base">Start writing free</a>
                        <a href="{{ route('login') }}" class="btn-ghost !px-6 !py-3 text-base">Try the demo</a>
                    </div>
                    <p class="mt-3 text-xs text-gray-400">Runs entirely on your own infrastructure.</p>
                </section>

                <section class="grid grid-cols-1 sm:grid-cols-3 gap-6 pb-24">
                    @foreach ([
                        ['🤖','AI drafts','Give it a topic and get an outline or a full draft. Works with OpenAI, Anthropic, or a local Ollama model.'],
                        ['📊','Live SEO score','The score updates as you type: keyword density, heading structure, readability, meta length, and word count.'],
                        ['🔒','Self-hosted','MIT-licensed and runs on your own server. Your content and API keys stay with you.'],
                    ] as [$icon, $title, $body])
                        <div class="card p-6 hover-lift">
                            <div class="text-2xl">{{ $icon }}</div>
                            <h3 class="mt-3 font-bold text-gray-900 dark:text-white">{{ $title }}</h3>
                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ $body }}</p>
                        </div>
                    @endforeach
                </section>
            </main>
        </div>
    </body>
</html>
