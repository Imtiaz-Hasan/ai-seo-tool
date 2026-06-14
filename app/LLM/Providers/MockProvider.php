<?php

namespace App\LLM\Providers;

use App\LLM\Contracts\LlmProvider;
use App\LLM\Data\LlmRequest;
use App\LLM\Data\LlmResponse;

/**
 * Returns canned markdown with no network call, used for demo mode and tests.
 * It reads the Topic / Focus keyword / Target length lines that Prompts.php puts
 * in the prompt (and whether an outline or draft was requested) and builds an
 * article around them so the editor and score have real content to work with.
 */
class MockProvider implements LlmProvider
{
    public function name(): string
    {
        return 'mock';
    }

    public function generate(LlmRequest $request): LlmResponse
    {
        $topic = $this->field($request->prompt, 'Topic') ?: 'Your Topic';
        $keyword = $this->field($request->prompt, 'Focus keyword') ?: $topic;
        $target = (int) ($this->field($request->prompt, 'Target length') ?: 1200);
        $isOutline = stripos($request->prompt, 'outline') !== false;

        $text = $isOutline
            ? $this->outline($topic, $keyword)
            : $this->draft($topic, $keyword, max(600, $target));

        // Rough token counts for the usage display.
        $out = (int) round(str_word_count($text) * 1.3);

        return new LlmResponse(
            text: $text,
            model: 'mock-1',
            inputTokens: (int) round(str_word_count($request->prompt) * 1.3),
            outputTokens: $out,
        );
    }

    private function outline(string $topic, string $keyword): string
    {
        $kw = ucfirst($keyword);

        return <<<MD
        # {$topic}: The Complete Guide

        ## Introduction
        - What {$keyword} means and why it matters
        - Who this guide is for

        ## Understanding {$kw}
        - Core concepts explained simply
        - Common misconceptions

        ## How to Get Started with {$kw}
        - Step-by-step first actions
        - Tools and resources you'll need

        ## Best Practices for {$kw}
        - Proven techniques that work
        - Mistakes to avoid

        ## Measuring Success
        - Key metrics to track
        - How to iterate and improve

        ## Conclusion
        - Recap of the key takeaways
        - Your next steps
        MD;
    }

    private function draft(string $topic, string $keyword, int $target): string
    {
        $kw = $keyword;
        $kwU = ucfirst($keyword);
        $sections = [
            ["Why {$kwU} Matters", "Understanding {$kw} is the foundation of any successful strategy. When you focus on {$kw}, you give your audience exactly what they are searching for, and search engines reward that relevance with better visibility."],
            ["Getting Started with {$kwU}", "The best way to approach {$kw} is to start small and build momentum. Begin by mapping out your goals, then choose one tactic and execute it well before moving on to the next. Consistency beats intensity every time."],
            ["Best Practices for {$kwU}", "A few principles make {$kw} far more effective. Write for people first, keep your structure clear with descriptive headings, and use your focus keyword naturally rather than forcing it. Quality and clarity win over clever tricks."],
            ['Common Mistakes to Avoid', "Many people undermine their {$kw} efforts with avoidable errors: thin content, missing headings, or stuffing the same phrase repeatedly. Aim for a natural rhythm and a genuinely useful piece, and these problems disappear."],
            ['Measuring Your Results', "Track a handful of meaningful metrics so you know whether your {$kw} work is paying off. Watch how your content performs over time, learn from what resonates, and refine your approach with each new piece."],
        ];

        $md = "# {$topic}: A Practical Guide to {$kwU}\n\n";
        $md .= "If you want to master {$kw}, you are in the right place. This guide walks through everything you need to "
            ."understand {$kw}, apply it with confidence, and measure the results - in plain language, with no fluff.\n\n";

        // Add sections until we comfortably pass the target word count.
        $i = 0;
        while (str_word_count(strip_tags($md)) < $target && $i < 40) {
            [$h, $body] = $sections[$i % count($sections)];
            $md .= "## {$h}\n\n{$body}\n\n";
            $md .= "In practice, treat {$kw} as an ongoing process rather than a one-off task. Small, deliberate "
                ."improvements compound, and before long you will see the difference in both engagement and rankings.\n\n";
            $i++;
        }

        $md .= "## Conclusion\n\n";
        $md .= "{$kwU} rewards people who stay curious and consistent. Apply the ideas above, keep your writing clear "
            ."and reader-focused, and revisit your content as you learn more. That is how lasting results are built.\n";

        return $md;
    }

    /** Pull a "Label: value" line out of the prompt. */
    private function field(string $prompt, string $label): ?string
    {
        if (preg_match('/'.preg_quote($label, '/').':\s*(.+)/i', $prompt, $m)) {
            return trim($m[1]);
        }

        return null;
    }
}
