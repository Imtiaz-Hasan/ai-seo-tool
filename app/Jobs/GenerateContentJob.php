<?php

namespace App\Jobs;

use App\Models\Generation;
use App\Services\ContentGenerator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Runs one AI generation asynchronously and records the result on the Generation
 * row. With the Mock provider this is effectively instant; with a real provider
 * the editor polls the generation's status until it's done.
 */
class GenerateContentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [5, 15, 30];

    public int $timeout = 180;

    public function __construct(public int $generationId) {}

    public function handle(ContentGenerator $generator): void
    {
        $generation = Generation::find($this->generationId);
        if (! $generation || $generation->status === 'done') {
            return;
        }

        $generation->update(['status' => 'processing']);

        $user = $generation->user;

        try {
            $response = $generator->generate(
                $generation->type,
                $generation->topic,
                $generation->keyword ?? $generation->topic,
                $generation->target_word_count,
                $user,
            );

            $generation->update([
                'status' => 'done',
                'result' => $response->text,
                'provider' => $generator->providerName($user),
                'model' => $response->model,
                'input_tokens' => $response->inputTokens,
                'output_tokens' => $response->outputTokens,
                'error' => null,
            ]);
        } catch (\Throwable $e) {
            $generation->update(['status' => 'failed', 'error' => $e->getMessage()]);

            throw $e;
        }
    }

    public function failed(\Throwable $e): void
    {
        Generation::whereKey($this->generationId)->update(['status' => 'failed', 'error' => $e->getMessage()]);
    }
}
