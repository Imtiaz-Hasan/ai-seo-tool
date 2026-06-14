<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-2xl text-gray-900 dark:text-white tracking-tight">Content</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Your drafts and their SEO scores.</p>
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

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if ($pieces->isEmpty())
            <div class="card p-12 text-center animate-rise">
                <span class="mx-auto grid place-items-center h-16 w-16 rounded-2xl bg-gradient-to-br from-violet-500 to-indigo-600 text-white shadow-lg">
                    <x-application-logo class="h-8 w-8" />
                </span>
                <h3 class="mt-6 text-lg font-bold text-gray-900 dark:text-white">Write your first piece</h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 max-w-sm mx-auto">Start from a topic and let AI draft it, or write your own - with a live SEO score guiding every edit.</p>
                <form method="POST" action="{{ route('pieces.store') }}" class="mt-6">
                    @csrf
                    <button class="btn-primary">Create content</button>
                </form>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach ($pieces as $piece)
                    <div class="card hover-lift p-5 flex flex-col animate-rise">
                        <div class="flex items-start justify-between gap-3">
                            <a href="{{ route('pieces.edit', $piece) }}" class="font-semibold text-gray-900 dark:text-white hover:text-violet-600 dark:hover:text-violet-400 line-clamp-2">{{ $piece->title }}</a>
                            @php($score = $piece->last_score)
                            <span class="chip shrink-0 {{ $score === null ? 'bg-gray-100 text-gray-500 dark:bg-white/5 dark:text-gray-400' : ($score >= 80 ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-400' : ($score >= 50 ? 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-400' : 'bg-rose-100 text-rose-700 dark:bg-rose-500/15 dark:text-rose-400')) }}">
                                {{ $score === null ? '-' : $score }}
                            </span>
                        </div>
                        <div class="mt-2 text-sm text-gray-500 dark:text-gray-400 flex-1">
                            @if ($piece->target_keyword)
                                <span class="chip bg-violet-50 text-violet-700 dark:bg-violet-500/10 dark:text-violet-300">#{{ $piece->target_keyword }}</span>
                            @else
                                <span class="text-gray-400">No focus keyword</span>
                            @endif
                        </div>
                        <div class="mt-4 flex items-center justify-between text-xs text-gray-400">
                            <span>{{ \App\Seo\Text::wordCount($piece->body ?? '') }} words · updated {{ $piece->updated_at->diffForHumans() }}</span>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('pieces.edit', $piece) }}" class="text-violet-600 dark:text-violet-400 hover:underline font-medium">Edit</a>
                                <form method="POST" action="{{ route('pieces.destroy', $piece) }}" onsubmit="return confirm('Delete this piece?')">
                                    @csrf @method('DELETE')
                                    <button class="text-rose-500 hover:underline">Delete</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
