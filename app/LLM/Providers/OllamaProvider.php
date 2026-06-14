<?php

namespace App\LLM\Providers;

use App\LLM\Contracts\LlmProvider;
use App\LLM\Data\LlmRequest;
use App\LLM\Data\LlmResponse;
use App\LLM\LlmException;
use Illuminate\Support\Facades\Http;

/**
 * Local models via Ollama (https://ollama.com) - no API key, runs on your
 * machine. Point OLLAMA_BASE_URL at your Ollama server and pick any pulled model.
 */
class OllamaProvider implements LlmProvider
{
    public function __construct(private array $config) {}

    public function name(): string
    {
        return 'ollama';
    }

    public function generate(LlmRequest $request): LlmResponse
    {
        $model = $request->model ?? $this->config['model'];

        $response = Http::timeout(config('llm.timeout', 120))
            ->post(rtrim($this->config['base_url'], '/').'/api/chat', [
                'model' => $model,
                'stream' => false,
                'messages' => [
                    ['role' => 'system', 'content' => $request->system],
                    ['role' => 'user', 'content' => $request->prompt],
                ],
                'options' => [
                    'temperature' => $request->temperature ?? config('llm.temperature'),
                    'num_predict' => $request->maxTokens ?? config('llm.max_tokens'),
                ],
            ]);

        if ($response->failed()) {
            throw new LlmException('Ollama request failed ('.$response->status().'): '.$response->body());
        }

        $json = $response->json();

        return new LlmResponse(
            text: trim($json['message']['content'] ?? ''),
            model: $json['model'] ?? $model,
            inputTokens: $json['prompt_eval_count'] ?? 0,
            outputTokens: $json['eval_count'] ?? 0,
        );
    }
}
