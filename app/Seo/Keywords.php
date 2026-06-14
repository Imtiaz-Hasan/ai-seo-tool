<?php

namespace App\Seo;

/**
 * Focus-keyword occurrence and density over plain text.
 */
class Keywords
{
    public static function occurrences(string $plainText, string $keyword): int
    {
        $keyword = trim($keyword);
        if ($keyword === '') {
            return 0;
        }

        return substr_count(strtolower($plainText), strtolower($keyword));
    }

    /**
     * Density as a percentage of total words, accounting for multi-word keywords:
     * (occurrences × words-in-keyword) / total-words × 100.
     */
    public static function density(string $plainText, string $keyword): float
    {
        $totalWords = str_word_count($plainText);
        if ($totalWords === 0 || trim($keyword) === '') {
            return 0.0;
        }

        $keywordWords = max(1, str_word_count($keyword));
        $occurrences = self::occurrences($plainText, $keyword);

        return round(($occurrences * $keywordWords) / $totalWords * 100, 2);
    }
}
