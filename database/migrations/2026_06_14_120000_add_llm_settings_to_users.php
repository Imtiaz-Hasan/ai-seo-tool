<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-user LLM settings so each account can bring its own key / model instead of
 * relying on the install-wide .env config. Key columns are encrypted via the
 * model cast.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('llm_provider')->nullable();   // openai | anthropic | ollama (null = use install default)
            $table->text('openai_key')->nullable();
            $table->string('openai_model')->nullable();
            $table->text('anthropic_key')->nullable();
            $table->string('anthropic_model')->nullable();
            $table->string('ollama_base_url')->nullable();
            $table->string('ollama_model')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'llm_provider', 'openai_key', 'openai_model',
                'anthropic_key', 'anthropic_model', 'ollama_base_url', 'ollama_model',
            ]);
        });
    }
};
