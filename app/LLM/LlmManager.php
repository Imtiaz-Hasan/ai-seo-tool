<?php

namespace App\LLM;

use App\LLM\Contracts\LlmProvider;
use App\LLM\Providers\AnthropicProvider;
use App\LLM\Providers\MockProvider;
use App\LLM\Providers\OllamaProvider;
use App\LLM\Providers\OpenAiProvider;
use App\Models\User;

/**
 * Picks the active LlmProvider. Order of precedence:
 *   1. Demo mode -> always the mock provider.
 *   2. The given user's own provider + key, if they configured one.
 *   3. The install-wide config (.env).
 * If the chosen cloud provider has no key, it falls back to mock so the app
 * always works.
 */
class LlmManager
{
    public function provider(?User $user = null): LlmProvider
    {
        if (config('llm.demo_mode')) {
            return new MockProvider;
        }

        if ($user && $user->hasOwnLlm()) {
            [$name, $config] = $this->userConfig($user);

            return $this->make($name, $config);
        }

        $name = config('llm.provider', 'mock');

        return $this->make($name, config("llm.providers.{$name}", []));
    }

    public function isLive(?User $user = null): bool
    {
        return $this->provider($user)->name() !== 'mock';
    }

    /** @return array{0:string,1:array} the user's chosen provider name + merged config */
    private function userConfig(User $user): array
    {
        $name = $user->llm_provider;
        $base = config("llm.providers.{$name}", []);

        $config = match ($name) {
            'openai' => ['key' => $user->openai_key, 'base_url' => $base['base_url'], 'model' => $user->openai_model ?: $base['model']],
            'anthropic' => ['key' => $user->anthropic_key, 'base_url' => $base['base_url'], 'model' => $user->anthropic_model ?: $base['model'], 'version' => $base['version'] ?? '2023-06-01'],
            'ollama' => ['base_url' => $user->ollama_base_url ?: $base['base_url'], 'model' => $user->ollama_model ?: $base['model']],
            default => $base,
        };

        return [$name, $config];
    }

    private function make(string $name, array $config): LlmProvider
    {
        return match ($name) {
            'openai' => filled($config['key'] ?? null) ? new OpenAiProvider($config) : new MockProvider,
            'anthropic' => filled($config['key'] ?? null) ? new AnthropicProvider($config) : new MockProvider,
            'ollama' => new OllamaProvider($config),
            default => new MockProvider,
        };
    }
}
