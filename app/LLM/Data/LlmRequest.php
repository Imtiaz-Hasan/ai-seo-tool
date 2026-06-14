<?php

namespace App\LLM\Data;

/**
 * A provider-agnostic request: one system instruction, one user prompt, and a
 * few optional overrides. The app only does single-turn calls, so that's enough.
 */
class LlmRequest
{
    public function __construct(
        public readonly string $system,
        public readonly string $prompt,
        public readonly ?string $model = null,
        public readonly ?float $temperature = null,
        public readonly ?int $maxTokens = null,
    ) {}
}
