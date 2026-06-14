<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-gray-900 dark:text-white tracking-tight">Settings</h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Choose which model generates your content. Use your own key, or a local model.</p>
    </x-slot>

    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if ($demoMode)
            <div class="card border-violet-200/60 dark:border-violet-500/20 p-4 mb-5 text-sm text-violet-800 dark:text-violet-300">
                Demo mode is on, so generation always uses the built-in mock model. To use the settings below, set <code class="font-mono">DEMO_MODE=false</code> in <code class="font-mono">.env</code>.
            </div>
        @endif

        <form method="POST" action="{{ route('settings.update') }}" x-data="{ provider: '{{ $user->llm_provider ?? '' }}' }">
            @csrf
            @method('PUT')

            <div class="card p-6 space-y-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Provider</label>
                    <select name="llm_provider" x-model="provider" class="input">
                        <option value="">Use install default ({{ config('llm.provider') }})</option>
                        <option value="openai">OpenAI</option>
                        <option value="anthropic">Anthropic</option>
                        <option value="ollama">Ollama (local)</option>
                    </select>
                    <p class="mt-1 text-xs text-gray-400">Pick a provider to use your own key/model instead of the server's.</p>
                </div>

                {{-- OpenAI --}}
                <div x-show="provider === 'openai'" x-cloak class="space-y-3 border-t border-gray-100 dark:border-white/5 pt-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">
                            OpenAI API key
                            @if (filled($user->openai_key)) <span class="chip bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-400 ms-1">saved</span> @endif
                        </label>
                        <input type="password" name="openai_key" class="input font-mono" placeholder="sk-... (leave blank to keep saved key)" autocomplete="off">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Model</label>
                        <input name="openai_model" class="input" value="{{ $user->openai_model }}" placeholder="{{ config('llm.providers.openai.model') }}">
                    </div>
                </div>

                {{-- Anthropic --}}
                <div x-show="provider === 'anthropic'" x-cloak class="space-y-3 border-t border-gray-100 dark:border-white/5 pt-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">
                            Anthropic API key
                            @if (filled($user->anthropic_key)) <span class="chip bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-400 ms-1">saved</span> @endif
                        </label>
                        <input type="password" name="anthropic_key" class="input font-mono" placeholder="sk-ant-... (leave blank to keep saved key)" autocomplete="off">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Model</label>
                        <input name="anthropic_model" class="input" value="{{ $user->anthropic_model }}" placeholder="{{ config('llm.providers.anthropic.model') }}">
                    </div>
                </div>

                {{-- Ollama --}}
                <div x-show="provider === 'ollama'" x-cloak class="space-y-3 border-t border-gray-100 dark:border-white/5 pt-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Ollama base URL</label>
                        <input name="ollama_base_url" class="input font-mono" value="{{ $user->ollama_base_url }}" placeholder="http://localhost:11434">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Model</label>
                        <input name="ollama_model" class="input" value="{{ $user->ollama_model }}" placeholder="{{ config('llm.providers.ollama.model') }}">
                    </div>
                    <p class="text-xs text-gray-400">No key needed. Make sure the model is pulled (<code class="font-mono">ollama pull {{ config('llm.providers.ollama.model') }}</code>).</p>
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-gray-100 dark:border-white/5 pt-4">
                    <x-input-error :messages="$errors->all()" />
                    <button class="btn-primary">Save settings</button>
                </div>
            </div>
        </form>
    </div>

    <style>[x-cloak]{display:none!important}</style>
</x-app-layout>
