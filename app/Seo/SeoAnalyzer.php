<?php

namespace App\Seo;

use Illuminate\Support\Str;

/**
 * Scores markdown content against a focus keyword and target length, returning a
 * weighted 0-100 score and a tip per check. No framework deps, so it's easy to test.
 */
class SeoAnalyzer
{
    /** Per-check weights for the overall score. */
    private const WEIGHTS = [
        'word_count' => 2.0,
        'keyword_title' => 1.5,
        'keyword_density' => 2.0,
        'keyword_intro' => 1.0,
        'keyword_headings' => 1.0,
        'headings' => 1.5,
        'readability' => 1.5,
        'meta_title' => 1.0,
        'meta_description' => 1.0,
    ];

    public function analyze(
        string $markdown,
        string $keyword = '',
        int $targetWords = 1000,
        ?string $metaTitle = null,
        ?string $metaDescription = null,
    ): SeoReport {
        $keyword = trim($keyword);
        $plain = Text::plain($markdown);
        $wordCount = str_word_count($plain);
        $headings = Headings::parse($markdown);
        $h1 = Headings::title($headings) ?? '';
        $intro = Text::firstParagraph($markdown);
        $density = Keywords::density($plain, $keyword);
        $ease = Readability::readingEase($plain);

        $metaTitle = $metaTitle !== null ? trim($metaTitle) : '';
        $metaDescription = $metaDescription !== null ? trim($metaDescription) : '';
        $titleSuggestion = $metaTitle ?: Str::limit($h1 ?: 'Untitled', 57, '');
        $descSuggestion = $metaDescription ?: Str::limit($intro, 153, '');

        $checks = [];

        // 1) Word count vs target.
        $ratio = $targetWords > 0 ? $wordCount / $targetWords : 0;
        $checks[] = $this->check('word_count', 'Word count',
            $ratio >= 0.9 ? 100 : (int) round(max(0, min(100, $ratio / 0.9 * 100))),
            $ratio >= 0.9 ? 'pass' : ($ratio >= 0.5 ? 'warn' : 'fail'),
            $ratio >= 0.9
                ? "{$wordCount} words - on target."
                : "Only {$wordCount} of ~{$targetWords} target words. Add more depth.");

        // 2) Keyword checks (only when a focus keyword is set).
        if ($keyword !== '') {
            $inTitle = Text::containsKeyword($h1, $keyword);
            $checks[] = $this->check('keyword_title', 'Keyword in title',
                $inTitle ? 100 : 0, $inTitle ? 'pass' : 'fail',
                $inTitle ? 'Focus keyword appears in the H1 title.' : 'Add the focus keyword to your H1 title.');

            $checks[] = $this->keywordDensityCheck($density);

            $inIntro = Text::containsKeyword($intro, $keyword);
            $checks[] = $this->check('keyword_intro', 'Keyword in intro',
                $inIntro ? 100 : 0, $inIntro ? 'pass' : 'fail',
                $inIntro ? 'Focus keyword appears in the introduction.' : 'Mention the keyword in your first paragraph.');

            $inHeadings = collect($headings)->contains(fn ($h) => $h['level'] >= 2 && Text::containsKeyword($h['text'], $keyword));
            $checks[] = $this->check('keyword_headings', 'Keyword in subheadings',
                $inHeadings ? 100 : 50, $inHeadings ? 'pass' : 'warn',
                $inHeadings ? 'Keyword appears in at least one subheading.' : 'Use the keyword in at least one H2/H3.');
        }

        // 3) Heading structure.
        $h1Count = Headings::countAtLevel($headings, 1);
        $h2Count = Headings::countAtLevel($headings, 2);
        $structureScore = 100;
        if ($h1Count !== 1) {
            $structureScore -= 40;
        }
        if ($h2Count < 2) {
            $structureScore -= 30;
        }
        $structureScore = max(0, $structureScore);
        $checks[] = $this->check('headings', 'Heading structure', $structureScore,
            $structureScore >= 90 ? 'pass' : ($structureScore >= 60 ? 'warn' : 'fail'),
            match (true) {
                $h1Count !== 1 => "Use exactly one H1 (found {$h1Count}).",
                $h2Count < 2 => 'Break content into at least two H2 sections.',
                default => "Good structure: 1 H1 and {$h2Count} H2 sections.",
            });

        // 4) Readability.
        $readScore = match (true) {
            $ease >= 60 => 100,
            $ease >= 50 => 80,
            $ease >= 30 => (int) round(($ease - 30) / 20 * 50 + 30),
            default => 25,
        };
        $checks[] = $this->check('readability', 'Readability', $readScore,
            $ease >= 60 ? 'pass' : ($ease >= 30 ? 'warn' : 'fail'),
            'Flesch reading ease '.$ease.' ('.Readability::label($ease).'). '.
            ($ease >= 60 ? 'Easy to read.' : 'Shorten sentences and simplify wording.'));

        // 5) Meta title / description.
        $checks[] = $this->lengthCheck('meta_title', 'Meta title', strlen($titleSuggestion), 30, 60, $metaTitle === '');
        $checks[] = $this->lengthCheck('meta_description', 'Meta description', strlen($descSuggestion), 120, 160, $metaDescription === '');

        $overall = $this->weightedOverall($checks);

        return new SeoReport(
            overall: $overall,
            grade: SeoReport::gradeFor($overall),
            checks: $checks,
            stats: [
                'word_count' => $wordCount,
                'target_words' => $targetWords,
                'keyword_density' => $density,
                'reading_ease' => $ease,
                'reading_label' => Readability::label($ease),
                'grade_level' => Readability::gradeLevel($plain),
                'h1' => $h1Count,
                'h2' => $h2Count,
                'keyword' => $keyword,
            ],
            meta: [
                'title_suggestion' => $titleSuggestion,
                'description_suggestion' => $descSuggestion,
            ],
        );
    }

    private function keywordDensityCheck(float $density): array
    {
        [$score, $status, $msg] = match (true) {
            $density >= 0.5 && $density <= 2.5 => [100, 'pass', "Keyword density {$density}% - in the ideal 0.5-2.5% range."],
            $density > 2.5 && $density <= 4 => [60, 'warn', "Keyword density {$density}% is a little high; ease off slightly."],
            $density > 4 => [25, 'fail', "Keyword density {$density}% looks like keyword stuffing."],
            $density > 0 => [55, 'warn', "Keyword density {$density}% is low; use the keyword a bit more."],
            default => [0, 'fail', 'Focus keyword does not appear in the content.'],
        };

        return $this->check('keyword_density', 'Keyword density', $score, $status, $msg);
    }

    private function lengthCheck(string $key, string $label, int $len, int $min, int $max, bool $missing): array
    {
        if ($missing && $len === 0) {
            return $this->check($key, $label, 0, 'fail', "Add a {$label} ({$min}-{$max} characters).");
        }

        [$score, $status] = match (true) {
            $len >= $min && $len <= $max => [100, 'pass'],
            $len >= $min - 15 && $len <= $max + 15 => [65, 'warn'],
            default => [30, 'fail'],
        };
        $suffix = $missing ? ' (suggested - set your own)' : '';

        return $this->check($key, $label, $score, $status, "{$label} is {$len} chars; aim for {$min}-{$max}.{$suffix}");
    }

    private function check(string $key, string $label, int $score, string $status, string $message): array
    {
        return compact('key', 'label', 'score', 'status', 'message');
    }

    private function weightedOverall(array $checks): int
    {
        $sum = 0.0;
        $weightSum = 0.0;
        foreach ($checks as $c) {
            $w = self::WEIGHTS[$c['key']] ?? 1.0;
            $sum += $c['score'] * $w;
            $weightSum += $w;
        }

        return $weightSum > 0 ? (int) round($sum / $weightSum) : 0;
    }
}
