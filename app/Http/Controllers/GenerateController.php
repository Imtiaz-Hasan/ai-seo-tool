<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateContentJob;
use App\Models\ContentPiece;
use App\Models\Generation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Kicks off async AI generation (outline/draft) and reports status. The editor
 * polls show() until the generation is done, then inserts the markdown.
 */
class GenerateController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'type' => ['required', 'in:outline,draft'],
            'topic' => ['required', 'string', 'max:255'],
            'keyword' => ['nullable', 'string', 'max:120'],
            'target_word_count' => ['nullable', 'integer', 'min:50', 'max:20000'],
            'content_piece_id' => ['nullable', 'integer'],
        ]);

        // Only allow attaching to a piece the user owns.
        $pieceId = null;
        if (! empty($data['content_piece_id'])) {
            $piece = ContentPiece::find($data['content_piece_id']);
            if ($piece && $piece->user_id === $request->user()->id) {
                $pieceId = $piece->id;
            }
        }

        $generation = $request->user()->generations()->create([
            'content_piece_id' => $pieceId,
            'type' => $data['type'],
            'topic' => $data['topic'],
            'keyword' => $data['keyword'] ?? null,
            'target_word_count' => (int) ($data['target_word_count'] ?? 1000),
            'status' => 'queued',
        ]);

        GenerateContentJob::dispatch($generation->id);

        return response()->json(['id' => $generation->id, 'status' => $generation->fresh()->status]);
    }

    public function show(Request $request, Generation $generation): JsonResponse
    {
        abort_unless($generation->user_id === $request->user()->id, 403);

        return response()->json([
            'id' => $generation->id,
            'status' => $generation->status,
            'result' => $generation->status === 'done' ? $generation->result : null,
            'error' => $generation->error,
        ]);
    }
}
