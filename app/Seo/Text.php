<?php

namespace App\Seo;

/**
 * Markdown → plain text helpers used by the analyzer.
 */
class Text
{
    /** Strip markdown to readable plain text (for word count, readability, density). */
    public static function plain(string $markdown): string
    {
        $text = $markdown;
        $text = preg_replace('/```.*?```/s', ' ', $text);          // code fences
        $text = preg_replace('/`[^`]*`/', ' ', $text);             // inline code
        $text = preg_replace('/^#{1,6}\s+/m', '', $text);          // heading markers
        $text = preg_replace('/!?\[([^\]]*)\]\([^)]*\)/', '$1', $text); // links/images -> text
        $text = preg_replace('/[*_~>#-]+/', ' ', $text);           // emphasis / list / quote marks
        $text = preg_replace('/\s+/', ' ', $text);

        return trim($text);
    }

    public static function wordCount(string $markdown): int
    {
        return str_word_count(self::plain($markdown));
    }

    /** First real paragraph (skips headings and blank lines). */
    public static function firstParagraph(string $markdown): string
    {
        foreach (preg_split('/\r?\n\s*\r?\n/', $markdown) as $block) {
            $block = trim($block);
            if ($block === '' || preg_match('/^#{1,6}\s+/', $block)) {
                continue;
            }

            return self::plain($block);
        }

        return '';
    }

    public static function containsKeyword(string $haystack, string $keyword): bool
    {
        $keyword = trim($keyword);

        return $keyword !== '' && stripos($haystack, $keyword) !== false;
    }
}
