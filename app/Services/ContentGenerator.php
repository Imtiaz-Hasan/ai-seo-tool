<?php

namespace App\Services;

use App\LLM\Data\LlmResponse;
use App\LLM\LlmManager;
use App\LLM\Prompts;
use App\Models\User;

/**
 * Turns a topic/keyword into an outline or draft using the active LLM provider.
 * Maps the generation type to a prompt and calls the provider for the given user
 * (so per-user keys are honoured); falls back to the install default.
 */
class ContentGenerator
{
    public function __construct(private LlmManager $llm) {}

    public function generate(string $type, string $topic, string $keyword, int $targetWords, ?User $user = null): LlmResponse
    {
        $request = $type === 'outline'
            ? Prompts::outline($topic, $keyword ?: $topic)
            : Prompts::draft($topic, $keyword ?: $topic, $targetWords);

        return $this->llm->provider($user)->generate($request);
    }

    public function providerName(?User $user = null): string
    {
        return $this->llm->provider($user)->name();
    }
}
