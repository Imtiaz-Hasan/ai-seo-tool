<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Active LLM provider
    |--------------------------------------------------------------------------
    |
    | One of: mock | openai | anthropic | ollama. The app ALWAYS works:
    | if demo mode is on, or the selected provider has no API key configured,
    | the LlmManager transparently falls back to the Mock provider (canned,
    | realistic responses) so the tool is fully usable with no key.
    |
    | Bring your own key, or point at a local model (Ollama) - see the README.
    |
    */

    'provider' => env('LLM_PROVIDER', 'mock'),

    'demo_mode' => (bool) env('DEMO_MODE', false),

    'temperature' => (float) env('LLM_TEMPERATURE', 0.7),
    'max_tokens' => (int) env('LLM_MAX_TOKENS', 2000),
    'timeout' => (int) env('LLM_TIMEOUT', 120),

    'system_prompt' => 'You are an expert SEO content strategist and writer. You write '
        .'clear, well-structured, original content with strong heading hierarchy and natural '
        .'keyword usage. You never keyword-stuff and you write for humans first.',

    'providers' => [

        'openai' => [
            'key' => env('OPENAI_API_KEY'),
            'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
            'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
        ],

        'anthropic' => [
            'key' => env('ANTHROPIC_API_KEY'),
            'base_url' => env('ANTHROPIC_BASE_URL', 'https://api.anthropic.com/v1'),
            'model' => env('ANTHROPIC_MODEL', 'claude-haiku-4-5'),
            'version' => env('ANTHROPIC_VERSION', '2023-06-01'),
        ],

        // Local models via Ollama (http://localhost:11434). No key required.
        'ollama' => [
            'base_url' => env('OLLAMA_BASE_URL', 'http://localhost:11434'),
            'model' => env('OLLAMA_MODEL', 'llama3.1'),
        ],

    ],

];
