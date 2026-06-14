<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_save_their_llm_settings(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put(route('settings.update'), [
            'llm_provider' => 'openai',
            'openai_key' => 'sk-mykey',
            'openai_model' => 'gpt-4o',
        ])->assertRedirect();

        $user->refresh();
        $this->assertSame('openai', $user->llm_provider);
        $this->assertSame('sk-mykey', $user->openai_key); // decrypted via cast
        $this->assertSame('gpt-4o', $user->openai_model);
        $this->assertTrue($user->hasOwnLlm());
    }

    public function test_blank_key_keeps_the_saved_one(): void
    {
        $user = User::factory()->create(['llm_provider' => 'openai', 'openai_key' => 'sk-existing']);

        $this->actingAs($user)->put(route('settings.update'), [
            'llm_provider' => 'openai',
            'openai_key' => '',
        ]);

        $this->assertSame('sk-existing', $user->refresh()->openai_key);
    }

    public function test_keys_are_stored_encrypted(): void
    {
        $user = User::factory()->create(['openai_key' => 'sk-secret']);

        $raw = $user->getRawOriginal('openai_key');
        $this->assertNotSame('sk-secret', $raw);
        $this->assertSame('sk-secret', $user->openai_key);
    }

    public function test_settings_page_requires_auth(): void
    {
        $this->get(route('settings.edit'))->assertRedirect(route('login'));
    }
}
