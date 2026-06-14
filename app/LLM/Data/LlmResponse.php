<?php

namespace App\LLM\Data;

/**
 * The normalized result of a generation, regardless of provider.
 */
class LlmResponse
{
    public function __construct(
        public readonly string $text,
        public readonly string $model,
        public readonly int $inputTokens = 0,
        public readonly int $outputTokens = 0,
    ) {}
}
