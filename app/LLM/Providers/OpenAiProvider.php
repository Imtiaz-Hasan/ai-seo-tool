<?php

namespace App\LLM\Providers;

use App\LLM\Contracts\LlmProvider;
use App\LLM\Data\LlmRequest;
use App\LLM\Data\LlmResponse;
use App\LLM\LlmException;
use Illuminate\Support\Facades\Http;

/**
 * OpenAI Chat Completions provider. Also works with any OpenAI-compatible
 * endpoint (LM Studio, vLLM, OpenRouter…) by overriding OPENAI_BASE_URL.
 */
class OpenAiProvider implements LlmProvider
{
    public function __construct(private array $config) {}

    public function name(): string
    {
        return 'openai';
    }

    public function generate(LlmRequest $request): LlmResponse
    {
        $model = $request->model ?? $this->config['model'];

        $response = Http::withToken($this->config['key'])
            ->timeout(config('llm.timeout', 120))
            ->retry(2, 1500, throw: false)
            ->post(rtrim($this->config['base_url'], '/').'/chat/completions', [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => $request->system],
                    ['role' => 'user', 'content' => $request->prompt],
                ],
                'temperature' => $request->temperature ?? config('llm.temperature'),
                'max_tokens' => $request->maxTokens ?? config('llm.max_tokens'),
            ]);

        if ($response->failed()) {
            throw new LlmException('OpenAI request failed ('.$response->status().'): '.$response->body());
        }

        $json = $response->json();

        return new LlmResponse(
            text: trim($json['choices'][0]['message']['content'] ?? ''),
            model: $json['model'] ?? $model,
            inputTokens: $json['usage']['prompt_tokens'] ?? 0,
            outputTokens: $json['usage']['completion_tokens'] ?? 0,
        );
    }
}
