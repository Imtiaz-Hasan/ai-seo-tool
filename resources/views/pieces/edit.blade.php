<x-app-layout>
    <div x-data="editor(@js($piece), @js($report))" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

        {{-- Toolbar --}}
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
            <div class="flex items-center gap-3 min-w-0 flex-1">
                <a href="{{ route('pieces.index') }}" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 shrink-0" title="Back">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <input x-model="title" @input.debounce.800ms="save(true)"
                       class="w-full bg-transparent text-2xl font-bold text-gray-900 dark:text-white tracking-tight border-0 focus:ring-0 px-0 placeholder-gray-400"
                       placeholder="Untitled draft">
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <span class="text-xs text-gray-400" x-text="savedLabel"></span>
                <button @click="genOpen = true" class="btn-ghost !py-2">
                    <svg class="h-4 w-4 text-violet-500" fill="currentColor" viewBox="0 0 24 24"><path d="M11 2l1.6 5.2L18 9l-5.4 1.8L11 16l-1.6-5.2L4 9l5.4-1.8L11 2z"/></svg>
                    Generate with AI
                </button>
                <button @click="save()" class="btn-primary !py-2" :disabled="saving">
                    <span x-show="!saving">Save</span><span x-show="saving">Saving…</span>
                </button>
            </div>
        </div>

        {{-- Targets --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-5">
            <div>
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Focus keyword</label>
                <input x-model="keyword" @input.debounce.400ms="score()" class="input" placeholder="e.g. content marketing strategy">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Target word count</label>
                <input x-model.number="target" @input.debounce.400ms="score()" type="number" min="50" class="input">
            </div>
        </div>

        {{-- Split screen --}}
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
            {{-- Editor --}}
            <div class="lg:col-span-3 card p-1.5 flex flex-col">
                <div class="flex items-center justify-between px-2.5 pt-1.5 pb-1">
                    <div class="inline-flex rounded-lg bg-gray-100 dark:bg-white/5 p-0.5 text-sm">
                        <button type="button" @click="view='write'" :class="view==='write' ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm' : 'text-gray-500 dark:text-gray-400'" class="px-3 py-1 rounded-md font-medium">Write</button>
                        <button type="button" @click="view='preview'" :class="view==='preview' ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm' : 'text-gray-500 dark:text-gray-400'" class="px-3 py-1 rounded-md font-medium">Preview</button>
                    </div>
                    <div class="flex items-center gap-1.5 pe-1">
                        <button type="button" @click="copy()" class="text-xs font-medium text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 px-2 py-1 rounded-md hover:bg-gray-100 dark:hover:bg-white/5">Copy</button>
                        <button type="button" @click="download()" class="text-xs font-medium text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 px-2 py-1 rounded-md hover:bg-gray-100 dark:hover:bg-white/5">Download .md</button>
                    </div>
                </div>
                <textarea x-show="view==='write'" x-model="body" @input.debounce.350ms="score()"
                          class="w-full h-[60vh] lg:h-[66vh] resize-none rounded-xl border-0 bg-transparent font-mono text-[15px] leading-7 text-gray-800 dark:text-gray-100 focus:ring-0 p-4 placeholder-gray-400"
                          placeholder="# Start writing your article in markdown…&#10;&#10;Use ## headings, paragraphs, and your focus keyword naturally. Or hit “Generate with AI”."></textarea>
                <div x-show="view==='preview'" x-cloak x-html="previewHtml()"
                     class="md-preview w-full h-[60vh] lg:h-[66vh] overflow-auto p-4 text-gray-800 dark:text-gray-200"></div>
            </div>

            {{-- Live score panel --}}
            <div class="lg:col-span-2 space-y-5">
                <div class="card p-6 flex flex-col items-center text-center animate-rise">
                    <x-score-ring value="report.overall" :size="190" />
                    <div class="mt-2 chip"
                         :class="report.overall >= 80 ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-400' : report.overall >= 50 ? 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-400' : 'bg-rose-100 text-rose-700 dark:bg-rose-500/15 dark:text-rose-400'">
                        Grade <span x-text="report.grade" class="ms-1"></span>
                    </div>
                    <div class="mt-4 grid grid-cols-3 gap-3 w-full text-center">
                        <div><div class="text-lg font-bold text-gray-900 dark:text-white" x-text="report.stats.word_count"></div><div class="text-[11px] text-gray-400">words</div></div>
                        <div><div class="text-lg font-bold text-gray-900 dark:text-white" x-text="report.stats.keyword_density + '%'"></div><div class="text-[11px] text-gray-400">density</div></div>
                        <div><div class="text-lg font-bold text-gray-900 dark:text-white" x-text="report.stats.reading_ease"></div><div class="text-[11px] text-gray-400">readability</div></div>
                    </div>
                </div>

                {{-- Checklist --}}
                <div class="card p-5 animate-rise">
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-3">Suggestions</h3>
                    <ul class="space-y-2.5">
                        <template x-for="check in report.checks" :key="check.key">
                            <li class="flex items-start gap-2.5 text-sm">
                                <span class="mt-0.5 shrink-0"
                                      :class="check.status === 'pass' ? 'text-emerald-500' : check.status === 'warn' ? 'text-amber-500' : 'text-rose-500'">
                                    <template x-if="check.status === 'pass'"><svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 010 1.4l-7.5 7.5a1 1 0 01-1.4 0L3.3 9.7a1 1 0 011.4-1.4l3.3 3.3 6.8-6.8a1 1 0 011.4 0z" clip-rule="evenodd"/></svg></template>
                                    <template x-if="check.status === 'warn'"><svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a1 1 0 01.9.55l7 13A1 1 0 0117 17H3a1 1 0 01-.9-1.45l7-13A1 1 0 0110 2zm0 5a1 1 0 00-1 1v3a1 1 0 102 0V8a1 1 0 00-1-1zm0 7.5a1 1 0 100 2 1 1 0 000-2z"/></svg></template>
                                    <template x-if="check.status === 'fail'"><svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.7 7.3a1 1 0 00-1.4 1.4L8.6 10l-1.3 1.3a1 1 0 101.4 1.4L10 11.4l1.3 1.3a1 1 0 001.4-1.4L11.4 10l1.3-1.3a1 1 0 00-1.4-1.4L10 8.6 8.7 7.3z" clip-rule="evenodd"/></svg></template>
                                </span>
                                <div>
                                    <span class="font-medium text-gray-800 dark:text-gray-200" x-text="check.label"></span>
                                    <span class="text-gray-500 dark:text-gray-400" x-text="'- ' + check.message"></span>
                                </div>
                            </li>
                        </template>
                    </ul>
                </div>

                {{-- Meta suggestions --}}
                <div class="card p-5 animate-rise">
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-3">Meta tags</h3>
                    <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Meta title <span class="text-gray-400" x-text="'(' + (metaTitle || report.meta.title_suggestion).length + ' chars)'"></span></label>
                    <input x-model="metaTitle" @input.debounce.400ms="score()" class="input mb-1 text-sm" :placeholder="report.meta.title_suggestion">
                    <button type="button" class="text-xs text-violet-600 dark:text-violet-400 hover:underline mb-3" @click="metaTitle = report.meta.title_suggestion; score()">Use suggestion</button>

                    <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Meta description <span class="text-gray-400" x-text="'(' + (metaDescription || report.meta.description_suggestion).length + ' chars)'"></span></label>
                    <textarea x-model="metaDescription" @input.debounce.400ms="score()" rows="2" class="input text-sm" :placeholder="report.meta.description_suggestion"></textarea>
                    <button type="button" class="text-xs text-violet-600 dark:text-violet-400 hover:underline mt-1" @click="metaDescription = report.meta.description_suggestion; score()">Use suggestion</button>
                </div>
            </div>
        </div>

        @include('pieces.partials.generate-modal')
    </div>

    @include('pieces.partials.editor-script')
</x-app-layout>
