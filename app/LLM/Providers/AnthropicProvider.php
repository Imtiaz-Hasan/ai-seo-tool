<?php

namespace App\LLM\Providers;

use App\LLM\Contracts\LlmProvider;
use App\LLM\Data\LlmRequest;
use App\LLM\Data\LlmResponse;
use App\LLM\LlmException;
use Illuminate\Support\Facades\Http;

/**
 * Anthropic Messages API provider (https://docs.anthropic.com).
 * The system instruction is a top-level `system` field; messages carry only
 * user/assistant turns.
 */
class AnthropicProvider implements LlmProvider
{
    public function __construct(private array $config) {}

    public function name(): string
    {
        return 'anthropic';
    }

    public function generate(LlmRequest $request): LlmResponse
    {
        $model = $request->model ?? $this->config['model'];

        $response = Http::withHeaders([
            'x-api-key' => $this->config['key'],
            'anthropic-version' => $this->config['version'] ?? '2023-06-01',
            'content-type' => 'application/json',
        ])
            ->timeout(config('llm.timeout', 120))
            ->retry(2, 1500, throw: false)
            ->post(rtrim($this->config['base_url'], '/').'/messages', [
                'model' => $model,
                'max_tokens' => $request->maxTokens ?? config('llm.max_tokens'),
                'temperature' => $request->temperature ?? config('llm.temperature'),
                'system' => $request->system,
                'messages' => [
                    ['role' => 'user', 'content' => $request->prompt],
                ],
            ]);

        if ($response->failed()) {
            throw new LlmException('Anthropic request failed ('.$response->status().'): '.$response->body());
        }

        $json = $response->json();

        return new LlmResponse(
            text: trim($json['content'][0]['text'] ?? ''),
            model: $json['model'] ?? $model,
            inputTokens: $json['usage']['input_tokens'] ?? 0,
            outputTokens: $json['usage']['output_tokens'] ?? 0,
        );
    }
}
