<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenerationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Force the mock provider so tests never touch a real API.
        config(['llm.provider' => 'mock', 'llm.demo_mode' => true]);
    }

    public function test_generation_creates_a_result_via_the_mock_provider(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('generate.store'), [
            'type' => 'draft',
            'topic' => 'Beginner SEO guide',
            'keyword' => 'seo basics',
            'target_word_count' => 300,
        ]);

        $response->assertOk()->assertJsonStructure(['id', 'status']);

        // Queue is sync in tests, so the job has already run.
        $this->assertDatabaseHas('generations', [
            'id' => $response->json('id'),
            'status' => 'done',
            'provider' => 'mock',
        ]);

        $show = $this->actingAs($user)->getJson(route('generate.show', $response->json('id')));
        $show->assertOk()->assertJsonPath('status', 'done');
        $this->assertNotEmpty($show->json('result'));
    }

    public function test_generation_requires_authentication(): void
    {
        $this->postJson(route('generate.store'), ['type' => 'draft', 'topic' => 'x'])
            ->assertUnauthorized();
    }

    public function test_users_cannot_view_another_users_generation(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $gen = $owner->generations()->create(['type' => 'draft', 'topic' => 'x', 'status' => 'done']);

        $this->actingAs($other)->getJson(route('generate.show', $gen))->assertForbidden();
    }
}
