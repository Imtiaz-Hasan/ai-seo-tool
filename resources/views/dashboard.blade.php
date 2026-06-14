<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-2xl text-gray-900 dark:text-white tracking-tight">Welcome back, {{ explode(' ', auth()->user()->name)[0] }} 👋</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Your recent content and SEO scores.</p>
            </div>
            <form method="POST" action="{{ route('pieces.store') }}">
                @csrf
                <button class="btn-primary">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/></svg>
                    New content
                </button>
            </form>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        {{-- Stats --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="card p-5 animate-rise">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Content pieces</div>
                <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $stats['pieces'] }}</div>
            </div>
            <div class="card p-5 animate-rise">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Average SEO score</div>
                <div class="mt-2 text-3xl font-bold gradient-text inline-block">{{ $stats['avg_score'] }}</div>
            </div>
            <div class="card p-5 animate-rise">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">AI generations</div>
                <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $stats['generations'] }}</div>
            </div>
            <div class="card p-5 animate-rise">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Tokens generated</div>
                <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['tokens']) }}</div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Recent pieces --}}
            <div class="lg:col-span-2 card overflow-hidden animate-rise">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-white/5 flex items-center justify-between">
                    <h3 class="font-semibold text-gray-900 dark:text-white">Recent content</h3>
                    <a href="{{ route('pieces.index') }}" class="text-sm text-violet-600 dark:text-violet-400 hover:underline">View all</a>
                </div>
                @forelse ($pieces as $piece)
                    <a href="{{ route('pieces.edit', $piece) }}" class="flex items-center justify-between gap-4 px-5 py-3.5 border-b border-gray-100 dark:border-white/5 last:border-0 hover:bg-gray-50/70 dark:hover:bg-white/5 transition">
                        <div class="min-w-0">
                            <div class="font-medium text-gray-900 dark:text-white truncate">{{ $piece->title }}</div>
                            <div class="text-xs text-gray-400 truncate">{{ $piece->target_keyword ? '#'.$piece->target_keyword : 'No keyword' }} · {{ $piece->updated_at->diffForHumans() }}</div>
                        </div>
                        @php($score = $piece->last_score)
                        <span class="chip shrink-0 {{ $score === null ? 'bg-gray-100 text-gray-500 dark:bg-white/5 dark:text-gray-400' : ($score >= 80 ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-400' : ($score >= 50 ? 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-400' : 'bg-rose-100 text-rose-700 dark:bg-rose-500/15 dark:text-rose-400')) }}">{{ $score ?? '-' }}</span>
                    </a>
                @empty
                    <div class="px-5 py-12 text-center text-sm text-gray-400">No content yet - hit “New content” to start.</div>
                @endforelse
            </div>

            {{-- Recent generations --}}
            <div class="card overflow-hidden animate-rise">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-white/5">
                    <h3 class="font-semibold text-gray-900 dark:text-white">AI history</h3>
                </div>
                @forelse ($recentGenerations as $gen)
                    <div class="px-5 py-3 border-b border-gray-100 dark:border-white/5 last:border-0">
                        <div class="flex items-center gap-2">
                            <span class="chip {{ $gen->type === 'outline' ? 'bg-sky-100 text-sky-700 dark:bg-sky-500/15 dark:text-sky-300' : 'bg-violet-100 text-violet-700 dark:bg-violet-500/15 dark:text-violet-300' }}">{{ $gen->type }}</span>
                            <span class="text-xs text-gray-400">{{ $gen->created_at->diffForHumans() }}</span>
                        </div>
                        <div class="mt-1 text-sm text-gray-700 dark:text-gray-300 truncate">{{ $gen->topic }}</div>
                    </div>
                @empty
                    <div class="px-5 py-12 text-center text-sm text-gray-400">No generations yet.</div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
