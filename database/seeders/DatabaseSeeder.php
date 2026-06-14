<?php

namespace Database\Seeders;

use App\Models\User;
use App\Seo\SeoAnalyzer;
use App\Services\ContentGenerator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Always seed the admin / demo account (idempotent).
        $user = User::updateOrCreate(
            ['email' => config('content.admin.email')],
            [
                'name' => config('content.admin.name'),
                'password' => Hash::make(config('content.admin.password')),
                'email_verified_at' => now(),
            ],
        );

        // Only fill demo content in demo mode, and only once.
        if (! config('content.demo_mode') || $user->contentPieces()->exists()) {
            return;
        }

        $generator = app(ContentGenerator::class); // Mock provider in demo mode
        $analyzer = new SeoAnalyzer;

        $samples = [
            ['Content Marketing Strategy: A Complete Guide', 'content marketing strategy'],
            ['SEO Basics for Beginners', 'seo basics'],
            ['How to Grow an Email Newsletter', 'email newsletter'],
        ];

        foreach ($samples as [$topic, $keyword]) {
            $response = $generator->generate('draft', $topic, $keyword, 1000);
            $report = $analyzer->analyze($response->text, $keyword, 1000);

            $piece = $user->contentPieces()->create([
                'title' => $topic,
                'target_keyword' => $keyword,
                'target_word_count' => 1000,
                'body' => $response->text,
                'meta_title' => Str::limit($topic, 57, ''),
                'meta_description' => $report->meta['description_suggestion'],
                'last_score' => $report->overall,
            ]);

            $user->generations()->create([
                'content_piece_id' => $piece->id,
                'type' => 'draft',
                'topic' => $topic,
                'keyword' => $keyword,
                'target_word_count' => 1000,
                'status' => 'done',
                'result' => $response->text,
                'provider' => 'mock',
                'model' => $response->model,
                'output_tokens' => $response->outputTokens,
            ]);
        }
    }
}
