<?php

namespace App\Seo;

/**
 * Immutable result of an SEO analysis: the overall 0-100 score, a letter grade,
 * the individual checks (each with a pass/warn/fail status + tip), some stats for
 * the UI, and meta title/description suggestions.
 */
class SeoReport
{
    public function __construct(
        public readonly int $overall,
        public readonly string $grade,
        public readonly array $checks,
        public readonly array $stats,
        public readonly array $meta,
    ) {}

    public static function gradeFor(int $score): string
    {
        return match (true) {
            $score >= 90 => 'A',
            $score >= 75 => 'B',
            $score >= 60 => 'C',
            $score >= 40 => 'D',
            default => 'F',
        };
    }

    public function toArray(): array
    {
        return [
            'overall' => $this->overall,
            'grade' => $this->grade,
            'checks' => $this->checks,
            'stats' => $this->stats,
            'meta' => $this->meta,
        ];
    }
}
