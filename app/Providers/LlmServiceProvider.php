<?php

namespace App\Providers;

use App\LLM\Contracts\LlmProvider;
use App\LLM\LlmManager;
use Illuminate\Support\ServiceProvider;

class LlmServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(LlmManager::class);

        // Resolve the contract to the currently-configured provider. Tests can
        // swap this binding (or set LLM_PROVIDER=mock) to avoid any network call.
        $this->app->bind(LlmProvider::class, fn ($app) => $app->make(LlmManager::class)->provider());
    }
}
