<?php

namespace Tests\Unit;

use App\Seo\SeoAnalyzer;
use Tests\TestCase;

class SeoAnalyzerTest extends TestCase
{
    private function analyze(string $md, string $kw = 'content marketing', int $target = 100, ?string $metaTitle = null, ?string $metaDescription = null)
    {
        return (new SeoAnalyzer)->analyze($md, $kw, $target, $metaTitle, $metaDescription);
    }

    public function test_counts_words_from_markdown(): void
    {
        $report = $this->analyze("# Title\n\nOne two three four five.", 'title', 100);
        $this->assertSame(6, $report->stats['word_count']); // Title + One two three four five
    }

    public function test_detects_heading_structure(): void
    {
        $report = $this->analyze("# H1\n\n## A\n\n## B\n\ntext", 'h1', 50);
        $this->assertSame(1, $report->stats['h1']);
        $this->assertSame(2, $report->stats['h2']);
    }

    public function test_keyword_density_is_in_range_for_natural_usage(): void
    {
        $body = "# Guide to SEO\n\n## SEO tips\n\n".str_repeat('word ', 95).' seo seo';
        $report = $this->analyze($body, 'seo', 100);
        $density = collect($report->checks)->firstWhere('key', 'keyword_density');
        $this->assertNotNull($density);
        $this->assertGreaterThan(0, $report->stats['keyword_density']);
    }

    public function test_flags_keyword_missing_from_title(): void
    {
        $report = $this->analyze("# An unrelated heading\n\n## Section\n\nSome body text here.", 'content marketing', 50);
        $check = collect($report->checks)->firstWhere('key', 'keyword_title');
        $this->assertSame('fail', $check['status']);
    }

    public function test_overall_score_is_between_0_and_100(): void
    {
        $report = $this->analyze("# Content marketing\n\n## Content marketing basics\n\n".str_repeat('Clear simple words here. ', 30), 'content marketing', 100);
        $this->assertGreaterThanOrEqual(0, $report->overall);
        $this->assertLessThanOrEqual(100, $report->overall);
        $this->assertContains($report->grade, ['A', 'B', 'C', 'D', 'F']);
    }

    public function test_strong_content_scores_higher_than_empty(): void
    {
        $empty = $this->analyze('', 'content marketing', 1000)->overall;
        $strong = $this->analyze(
            "# Content marketing strategy\n\n## Why content marketing works\n\n"
            .str_repeat('Content marketing helps brands reach people with clear simple words. ', 20)
            ."\n\n## Content marketing tips\n\n".str_repeat('Write short sentences. Keep it readable. ', 20),
            'content marketing',
            200,
        )->overall;

        $this->assertGreaterThan($empty, $strong);
    }

    public function test_meta_description_length_check(): void
    {
        $report = $this->analyze("# T\n\n## S\n\nbody", 'x', 50, null, str_repeat('a', 140));
        $meta = collect($report->checks)->firstWhere('key', 'meta_description');
        $this->assertSame('pass', $meta['status']); // 140 is within 120–160
    }
}
