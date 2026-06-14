<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_score_endpoint_returns_a_report(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('score'), [
            'body' => "# Content marketing\n\n## Content marketing basics\n\nClear, simple, readable words about content marketing.",
            'keyword' => 'content marketing',
            'target_word_count' => 50,
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'overall', 'grade',
                'checks' => [['key', 'label', 'score', 'status', 'message']],
                'stats' => ['word_count', 'keyword_density', 'reading_ease'],
                'meta' => ['title_suggestion', 'description_suggestion'],
            ]);

        $this->assertIsInt($response->json('overall'));
    }

    public function test_score_endpoint_requires_auth(): void
    {
        $this->postJson(route('score'), ['body' => 'x'])->assertUnauthorized();
    }
}
