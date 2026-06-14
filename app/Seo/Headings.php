<?php

namespace App\Seo;

/**
 * Parses ATX-style markdown headings (#, ##, ###…) from a document.
 */
class Headings
{
    /** @return array<int, array{level:int, text:string}> */
    public static function parse(string $markdown): array
    {
        $headings = [];
        foreach (preg_split('/\r?\n/', $markdown) as $line) {
            if (preg_match('/^(#{1,6})\s+(.+?)\s*#*\s*$/', $line, $m)) {
                $headings[] = ['level' => strlen($m[1]), 'text' => trim($m[2])];
            }
        }

        return $headings;
    }

    public static function countAtLevel(array $headings, int $level): int
    {
        return count(array_filter($headings, fn ($h) => $h['level'] === $level));
    }

    /** The first H1's text, if any. */
    public static function title(array $headings): ?string
    {
        foreach ($headings as $h) {
            if ($h['level'] === 1) {
                return $h['text'];
            }
        }

        return null;
    }
}
