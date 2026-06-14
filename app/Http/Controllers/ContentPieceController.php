<?php

namespace App\Http\Controllers;

use App\Models\ContentPiece;
use App\Seo\SeoAnalyzer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContentPieceController extends Controller
{
    public function index(Request $request): View
    {
        return view('pieces.index', [
            'pieces' => $request->user()->contentPieces()->get(),
        ]);
    }

    /** Create a blank piece and jump straight into the editor. */
    public function store(Request $request): RedirectResponse
    {
        $piece = $request->user()->contentPieces()->create([
            'title' => 'Untitled draft',
            'target_word_count' => config('content.default_target_words', 1000),
            'body' => '',
        ]);

        return redirect()->route('pieces.edit', $piece);
    }

    public function edit(Request $request, ContentPiece $piece, SeoAnalyzer $analyzer): View
    {
        $this->authorizeOwner($request, $piece);

        // Initial server-side score so the panel is populated on first paint.
        $report = $analyzer->analyze(
            $piece->body ?? '',
            $piece->target_keyword ?? '',
            $piece->target_word_count,
            $piece->meta_title,
            $piece->meta_description,
        );

        return view('pieces.edit', ['piece' => $piece, 'report' => $report->toArray()]);
    }

    public function update(Request $request, ContentPiece $piece, SeoAnalyzer $analyzer): RedirectResponse
    {
        $this->authorizeOwner($request, $piece);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'target_keyword' => ['nullable', 'string', 'max:120'],
            'target_word_count' => ['required', 'integer', 'min:50', 'max:20000'],
            'body' => ['nullable', 'string'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:320'],
        ]);

        $data['last_score'] = $analyzer->analyze(
            $data['body'] ?? '', $data['target_keyword'] ?? '', (int) $data['target_word_count'],
            $data['meta_title'] ?? null, $data['meta_description'] ?? null,
        )->overall;

        $piece->update($data);

        return back()->with('status', 'Saved.');
    }

    public function destroy(Request $request, ContentPiece $piece): RedirectResponse
    {
        $this->authorizeOwner($request, $piece);
        $piece->delete();

        return redirect()->route('pieces.index')->with('status', 'Content piece deleted.');
    }

    private function authorizeOwner(Request $request, ContentPiece $piece): void
    {
        abort_unless($piece->user_id === $request->user()->id, 403);
    }
}
