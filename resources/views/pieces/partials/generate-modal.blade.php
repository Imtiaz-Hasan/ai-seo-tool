{{-- AI generation modal (Alpine state lives in editor()). --}}
<div x-show="genOpen" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none">
    <div class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm" @click="genOpen = false"></div>
    <div class="relative card w-full max-w-lg p-6 animate-rise" @keydown.escape.window="genOpen = false">
        <div class="flex items-center gap-2 mb-5">
            <span class="grid place-items-center h-9 w-9 rounded-xl bg-gradient-to-br from-violet-600 to-indigo-600 text-white">
                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M11 2l1.6 5.2L18 9l-5.4 1.8L11 16l-1.6-5.2L4 9l5.4-1.8L11 2z"/></svg>
            </span>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Generate with AI</h3>
        </div>

        <div class="space-y-4">
            <div>
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Topic / title</label>
                <input x-model="gen.topic" class="input" placeholder="e.g. A beginner's guide to content marketing">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">What to generate</label>
                    <select x-model="gen.type" class="input">
                        <option value="draft">Full draft</option>
                        <option value="outline">Outline only</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Focus keyword</label>
                    <input x-model="gen.keyword" class="input" placeholder="optional">
                </div>
            </div>
            <p class="text-xs text-gray-400" x-show="generating">Generating with <span x-text="providerLabel"></span>… this can take a moment.</p>
            <p class="text-xs text-rose-500" x-show="genError" x-text="genError"></p>
        </div>

        <div class="mt-6 flex justify-end gap-2">
            <button class="btn-ghost !py-2" @click="genOpen = false" :disabled="generating">Cancel</button>
            <button class="btn-primary !py-2" @click="runGenerate()" :disabled="generating || !gen.topic">
                <svg x-show="generating" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>
                <span x-text="generating ? 'Generating…' : 'Generate'"></span>
            </button>
        </div>
    </div>
</div>
