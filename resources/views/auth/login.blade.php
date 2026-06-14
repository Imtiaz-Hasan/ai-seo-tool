<x-guest-layout>
    <div x-data="{ fillDemo() { this.$refs.email.value = '{{ config('content.admin.email') }}'; this.$refs.password.value = '{{ config('content.admin.password') }}'; } }">
        <div class="mb-6">
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Welcome back</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Sign in to your account.</p>
        </div>

        <x-auth-session-status class="mb-4" :status="session('status')" />

        @if (config('content.demo_mode'))
            <div class="mb-5 rounded-xl border border-violet-200 dark:border-violet-500/30 bg-violet-50 dark:bg-violet-500/10 px-4 py-3">
                <div class="flex items-center justify-between gap-3">
                    <div class="text-sm">
                        <p class="font-semibold text-violet-800 dark:text-violet-300">Demo account</p>
                        <p class="text-violet-700/80 dark:text-violet-300/70 font-mono text-xs mt-0.5">{{ config('content.admin.email') }} · {{ config('content.admin.password') }}</p>
                    </div>
                    <button type="button" @click="fillDemo()" class="text-xs font-semibold text-violet-700 dark:text-violet-300 hover:underline whitespace-nowrap">Autofill →</button>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Email</label>
                <input x-ref="email" id="email" class="input" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="you@example.com">
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Password</label>
                <input x-ref="password" id="password" class="input" type="password" name="password" required autocomplete="current-password" placeholder="••••••••">
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>
            <div class="flex items-center justify-between">
                <label for="remember_me" class="inline-flex items-center">
                    <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-violet-600 shadow-sm focus:ring-violet-500" name="remember">
                    <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">Remember me</span>
                </label>
                @if (Route::has('password.request'))
                    <a class="text-sm text-violet-600 dark:text-violet-400 hover:underline" href="{{ route('password.request') }}">Forgot password?</a>
                @endif
            </div>
            <button type="submit" class="btn-primary w-full">Log in</button>
        </form>

        @if (Route::has('register'))
            <p class="mt-6 text-center text-sm text-gray-500 dark:text-gray-400">
                New here? <a href="{{ route('register') }}" class="font-semibold text-violet-600 dark:text-violet-400 hover:underline">Create an account</a>
            </p>
        @endif
    </div>
</x-guest-layout>
