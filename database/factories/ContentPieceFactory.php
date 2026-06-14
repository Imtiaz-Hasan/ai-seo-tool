<?php

namespace Database\Factories;

use App\Models\ContentPiece;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContentPiece>
 */
class ContentPieceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => $this->faker->sentence(4),
            'target_keyword' => $this->faker->words(2, true),
            'target_word_count' => 1000,
            'body' => "# {$this->faker->sentence(5)}\n\n## "
                .$this->faker->sentence(3)."\n\n".$this->faker->paragraph(),
            'last_score' => $this->faker->numberBetween(40, 95),
        ];
    }
}
