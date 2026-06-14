<?php

namespace App\Http\Controllers;

use App\Seo\SeoAnalyzer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Stateless live-scoring endpoint. The editor debounce-posts the current text and
 * renders the returned report into the animated score ring + checklist.
 */
class ScoreController extends Controller
{
    public function __invoke(Request $request, SeoAnalyzer $analyzer): JsonResponse
    {
        $data = $request->validate([
            'body' => ['nullable', 'string'],
            'keyword' => ['nullable', 'string', 'max:120'],
            'target_word_count' => ['nullable', 'integer', 'min:50', 'max:20000'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:320'],
        ]);

        $report = $analyzer->analyze(
            $data['body'] ?? '',
            $data['keyword'] ?? '',
            (int) ($data['target_word_count'] ?? 1000),
            $data['meta_title'] ?? null,
            $data['meta_description'] ?? null,
        );

        return response()->json($report->toArray());
    }
}
