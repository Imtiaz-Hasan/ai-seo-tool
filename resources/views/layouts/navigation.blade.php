<nav x-data="{ open: false }" class="sticky top-0 z-40 bg-white/80 dark:bg-gray-900/70 backdrop-blur-lg border-b border-gray-200/70 dark:border-white/10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <a href="{{ route('dashboard') }}" class="shrink-0 flex items-center gap-2.5">
                    <span class="grid place-items-center h-9 w-9 rounded-xl bg-gradient-to-br from-violet-600 to-indigo-600 text-white shadow-sm">
                        <x-application-logo class="h-5 w-5" />
                    </span>
                    <span class="font-extrabold text-gray-900 dark:text-white tracking-tight">{{ config('app.name', 'Scribe') }}</span>
                </a>
                <div class="hidden sm:flex sm:items-center sm:ms-8 gap-1">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">{{ __('Dashboard') }}</x-nav-link>
                    <x-nav-link :href="route('pieces.index')" :active="request()->routeIs('pieces.*')">{{ __('Content') }}</x-nav-link>
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center gap-1.5">
                <form method="POST" action="{{ route('pieces.store') }}">
                    @csrf
                    <button class="btn-primary !py-2">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/></svg>
                        New content
                    </button>
                </form>
                <x-dark-toggle />
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center gap-2 ps-1.5 pe-3 py-1.5 rounded-full border border-gray-200 dark:border-white/10 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-white/5 focus:outline-none">
                            <span class="grid place-items-center h-7 w-7 rounded-full bg-gradient-to-br from-violet-500 to-fuchsia-500 text-white text-xs font-bold">{{ strtoupper(substr(Auth::user()?->name ?? 'U', 0, 1)) }}</span>
                            <span>{{ Auth::user()?->name }}</span>
                        </button>
                    </x-slot>
                    <x-slot name="content">
                        <x-dropdown-link :href="route('settings.edit')">{{ __('Settings') }}</x-dropdown-link>
                        <x-dropdown-link :href="route('profile.edit')">{{ __('Profile') }}</x-dropdown-link>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">{{ __('Log Out') }}</x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <div class="-me-2 flex items-center gap-1 sm:hidden">
                <x-dark-toggle />
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5 focus:outline-none">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden border-t border-gray-200/70 dark:border-white/10">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">{{ __('Dashboard') }}</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('pieces.index')" :active="request()->routeIs('pieces.*')">{{ __('Content') }}</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('pieces.store')" onclick="event.preventDefault(); this.closest('form')?.submit();">{{ __('+ New content') }}</x-responsive-nav-link>
        </div>
        <div class="pt-4 pb-1 border-t border-gray-200 dark:border-white/10">
            <div class="px-4 font-medium text-base text-gray-800 dark:text-gray-200">{{ Auth::user()?->name }}</div>
            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('settings.edit')">{{ __('Settings') }}</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('profile.edit')">{{ __('Profile') }}</x-responsive-nav-link>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">{{ __('Log Out') }}</x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
