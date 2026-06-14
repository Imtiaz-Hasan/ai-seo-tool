<?php

namespace Tests\Unit;

use App\LLM\Data\LlmRequest;
use App\LLM\LlmManager;
use App\LLM\Providers\AnthropicProvider;
use App\LLM\Providers\MockProvider;
use App\LLM\Providers\OllamaProvider;
use App\LLM\Providers\OpenAiProvider;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LlmProviderTest extends TestCase
{
    public function test_openai_provider_sends_expected_payload_and_parses_response(): void
    {
        Http::fake([
            '*/chat/completions' => Http::response([
                'model' => 'gpt-4o-mini',
                'choices' => [['message' => ['content' => 'Hello world']]],
                'usage' => ['prompt_tokens' => 11, 'completion_tokens' => 7],
            ]),
        ]);

        $provider = new OpenAiProvider(['key' => 'sk-test', 'base_url' => 'https://api.openai.com/v1', 'model' => 'gpt-4o-mini']);
        $response = $provider->generate(new LlmRequest('You are helpful.', 'Say hi'));

        $this->assertSame('Hello world', $response->text);
        $this->assertSame('gpt-4o-mini', $response->model);
        $this->assertSame(11, $response->inputTokens);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/chat/completions')
                && $request['model'] === 'gpt-4o-mini'
                && $request['messages'][0]['role'] === 'system'
                && $request['messages'][1]['content'] === 'Say hi';
        });
    }

    public function test_anthropic_provider_uses_messages_api_with_system_and_headers(): void
    {
        Http::fake([
            '*/messages' => Http::response([
                'model' => 'claude-haiku-4-5',
                'content' => [['type' => 'text', 'text' => 'Hi there']],
                'usage' => ['input_tokens' => 9, 'output_tokens' => 4],
            ]),
        ]);

        $provider = new AnthropicProvider(['key' => 'sk-ant-test', 'base_url' => 'https://api.anthropic.com/v1', 'model' => 'claude-haiku-4-5', 'version' => '2023-06-01']);
        $response = $provider->generate(new LlmRequest('System instruction.', 'Write something'));

        $this->assertSame('Hi there', $response->text);
        $this->assertSame(4, $response->outputTokens);

        Http::assertSent(function ($request) {
            return $request->hasHeader('x-api-key', 'sk-ant-test')
                && $request->hasHeader('anthropic-version', '2023-06-01')
                && $request['system'] === 'System instruction.'
                && $request['messages'][0]['role'] === 'user';
        });
    }

    public function test_ollama_provider_calls_local_chat_endpoint(): void
    {
        Http::fake([
            '*/api/chat' => Http::response([
                'model' => 'llama3.1',
                'message' => ['content' => 'Local answer'],
                'prompt_eval_count' => 5,
                'eval_count' => 6,
            ]),
        ]);

        $provider = new OllamaProvider(['base_url' => 'http://localhost:11434', 'model' => 'llama3.1']);
        $response = $provider->generate(new LlmRequest('sys', 'hi'));

        $this->assertSame('Local answer', $response->text);
        Http::assertSent(fn ($request) => str_contains($request->url(), '/api/chat') && $request['stream'] === false);
    }

    public function test_mock_provider_returns_keyword_aware_markdown(): void
    {
        $provider = new MockProvider;
        $response = $provider->generate(new LlmRequest('sys', "Write a draft.\nTopic: Running\nFocus keyword: trail running\nTarget length: 300 words"));

        $this->assertStringContainsString('#', $response->text);
        $this->assertStringContainsStringIgnoringCase('trail running', $response->text);
        $this->assertSame('mock-1', $response->model);
    }

    public function test_manager_falls_back_to_mock_without_a_key(): void
    {
        config(['llm.demo_mode' => false, 'llm.provider' => 'openai', 'llm.providers.openai.key' => null]);
        $this->assertSame('mock', (new LlmManager)->provider()->name());
    }

    public function test_manager_uses_openai_when_key_present(): void
    {
        config(['llm.demo_mode' => false, 'llm.provider' => 'openai', 'llm.providers.openai.key' => 'sk-test']);
        $this->assertSame('openai', (new LlmManager)->provider()->name());
    }

    public function test_demo_mode_always_uses_mock(): void
    {
        config(['llm.demo_mode' => true, 'llm.provider' => 'openai', 'llm.providers.openai.key' => 'sk-test']);
        $this->assertSame('mock', (new LlmManager)->provider()->name());
    }

    public function test_per_user_key_takes_precedence_over_install_config(): void
    {
        config(['llm.demo_mode' => false, 'llm.provider' => 'mock']);
        $user = new User(['llm_provider' => 'openai', 'openai_key' => 'sk-user', 'openai_model' => 'gpt-4o']);

        $this->assertSame('openai', (new LlmManager)->provider($user)->name());
    }

    public function test_user_without_a_key_falls_back(): void
    {
        config(['llm.demo_mode' => false, 'llm.provider' => 'mock']);
        $user = new User(['llm_provider' => 'openai']); // no key

        $this->assertSame('mock', (new LlmManager)->provider($user)->name());
    }

    public function test_demo_mode_overrides_even_a_user_key(): void
    {
        config(['llm.demo_mode' => true]);
        $user = new User(['llm_provider' => 'openai', 'openai_key' => 'sk-user']);

        $this->assertSame('mock', (new LlmManager)->provider($user)->name());
    }
}
