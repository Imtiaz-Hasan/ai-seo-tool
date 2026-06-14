<script>
    function editor(piece, initialReport) {
        const csrf = document.querySelector('meta[name="csrf-token"]').content;
        const json = (url, opts = {}) => fetch(url, {
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            ...opts,
        }).then(r => r.json());

        return {
            id: piece.id,
            title: piece.title ?? '',
            keyword: piece.target_keyword ?? '',
            target: piece.target_word_count ?? 1000,
            body: piece.body ?? '',
            metaTitle: piece.meta_title ?? '',
            metaDescription: piece.meta_description ?? '',
            report: initialReport,
            saving: false,
            savedLabel: '',
            view: 'write',
            // generate modal
            genOpen: false,
            generating: false,
            genError: '',
            providerLabel: @js(app(\App\LLM\LlmManager::class)->provider(auth()->user())->name() === 'mock' ? 'the demo model' : app(\App\LLM\LlmManager::class)->provider(auth()->user())->name()),
            gen: { type: 'draft', topic: '', keyword: '' },

            previewHtml() { return window.renderMarkdown(this.body); },

            async copy() {
                try { await navigator.clipboard.writeText(this.body); this.savedLabel = 'Copied to clipboard'; }
                catch (e) { this.savedLabel = 'Copy failed'; }
            },

            download() {
                const slug = (this.title || 'draft').toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '') || 'draft';
                const blob = new Blob([this.body], { type: 'text/markdown' });
                const a = document.createElement('a');
                a.href = URL.createObjectURL(blob);
                a.download = slug + '.md';
                a.click();
                URL.revokeObjectURL(a.href);
            },

            payload() {
                return {
                    body: this.body, keyword: this.keyword, target_word_count: this.target,
                    meta_title: this.metaTitle, meta_description: this.metaDescription,
                };
            },

            async score() {
                try {
                    this.report = await json('{{ route('score') }}', { method: 'POST', body: JSON.stringify(this.payload()) });
                } catch (e) { /* keep last report on transient error */ }
            },

            async save(silent = false) {
                this.saving = true;
                try {
                    await json('{{ route('pieces.update', $piece) }}', {
                        method: 'PUT',
                        body: JSON.stringify({ title: this.title || 'Untitled draft', target_keyword: this.keyword,
                            target_word_count: this.target, body: this.body, meta_title: this.metaTitle, meta_description: this.metaDescription }),
                    });
                    this.savedLabel = 'Saved ' + new Date().toLocaleTimeString();
                } finally { this.saving = false; }
            },

            async runGenerate() {
                this.generating = true; this.genError = '';
                if (!this.gen.keyword && this.keyword) this.gen.keyword = this.keyword;
                try {
                    const start = await json('{{ route('generate.store') }}', {
                        method: 'POST',
                        body: JSON.stringify({ type: this.gen.type, topic: this.gen.topic, keyword: this.gen.keyword,
                            target_word_count: this.target, content_piece_id: this.id }),
                    });
                    const result = await this.poll(start.id);
                    if (result.status === 'done') {
                        this.body = (this.body.trim() ? this.body.trim() + '\n\n' : '') + result.result;
                        if (!this.keyword && this.gen.keyword) this.keyword = this.gen.keyword;
                        if (this.title === 'Untitled draft' && this.gen.topic) this.title = this.gen.topic;
                        this.genOpen = false;
                        await this.score();
                        await this.save(true);
                    } else {
                        this.genError = result.error || 'Generation failed.';
                    }
                } catch (e) {
                    this.genError = 'Something went wrong. Please try again.';
                } finally { this.generating = false; }
            },

            async poll(id) {
                for (let i = 0; i < 90; i++) {
                    const g = await json('{{ url('generate') }}/' + id);
                    if (g.status === 'done' || g.status === 'failed') return g;
                    await new Promise(r => setTimeout(r, 1000));
                }
                return { status: 'failed', error: 'Timed out waiting for generation.' };
            },
        };
    }
</script>
