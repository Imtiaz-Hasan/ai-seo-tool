<?php

namespace App\Seo;

/**
 * Flesch readability metrics over plain text. The syllable count is a well-known
 * heuristic (vowel groups, minus silent trailing 'e', floor of 1) - good enough
 * for a writing-assistant score without an NLP dependency.
 */
class Readability
{
    /** Flesch Reading Ease (0-100; higher = easier). */
    public static function readingEase(string $text): float
    {
        [$words, $sentences, $syllables] = self::counts($text);
        if ($words === 0 || $sentences === 0) {
            return 0.0;
        }

        $ease = 206.835 - 1.015 * ($words / $sentences) - 84.6 * ($syllables / $words);

        return round(max(0, min(100, $ease)), 1);
    }

    /** Flesch-Kincaid US grade level. */
    public static function gradeLevel(string $text): float
    {
        [$words, $sentences, $syllables] = self::counts($text);
        if ($words === 0 || $sentences === 0) {
            return 0.0;
        }

        $grade = 0.39 * ($words / $sentences) + 11.8 * ($syllables / $words) - 15.59;

        return round(max(0, $grade), 1);
    }

    /** Human label for a reading-ease score. */
    public static function label(float $ease): string
    {
        return match (true) {
            $ease >= 80 => 'Very easy',
            $ease >= 60 => 'Easy',
            $ease >= 50 => 'Fairly readable',
            $ease >= 30 => 'Difficult',
            default => 'Very difficult',
        };
    }

    /** @return array{0:int,1:int,2:int} [words, sentences, syllables] */
    private static function counts(string $text): array
    {
        $text = trim($text);
        $words = preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $wordCount = count($words);

        $sentences = preg_split('/[.!?]+(?:\s|$)/', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $sentenceCount = max(1, count($sentences));

        $syllables = 0;
        foreach ($words as $word) {
            $syllables += self::syllables($word);
        }

        return [$wordCount, $sentenceCount, $syllables];
    }

    private static function syllables(string $word): int
    {
        $word = strtolower(preg_replace('/[^a-z]/i', '', $word));
        if ($word === '') {
            return 0;
        }

        $word = preg_replace('/e$/', '', $word);          // drop a silent trailing e
        preg_match_all('/[aeiouy]+/', $word, $groups);    // count vowel groups

        return max(1, count($groups[0]));
    }
}
