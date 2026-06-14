<?php

namespace App\LLM\Contracts;

use App\LLM\Data\LlmRequest;
use App\LLM\Data\LlmResponse;

/**
 * A pluggable Large Language Model provider. Implementations wrap a single
 * vendor (OpenAI, Anthropic, Ollama) or the canned Mock used in demo/tests.
 *
 * The rest of the app depends only on this interface, so swapping providers is
 * a config change - never a code change.
 */
interface LlmProvider
{
    public function generate(LlmRequest $request): LlmResponse;

    /** Short identifier, e.g. "openai", "mock". */
    public function name(): string;
}
