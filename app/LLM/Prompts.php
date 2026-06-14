<?php

namespace App\LLM;

use App\LLM\Data\LlmRequest;

/**
 * Builds the generation requests. Each prompt includes "Topic / Focus keyword /
 * Target length" lines, which real models read fine and the Mock provider parses.
 */
class Prompts
{
    public static function outline(string $topic, string $keyword): LlmRequest
    {
        $prompt = <<<TXT
        Create a detailed SEO content outline as markdown headings (no prose).

        Topic: {$topic}
        Focus keyword: {$keyword}

        Use a single H1 title, then H2 sections with short bullet points describing
        what each section covers. Make the structure logical and comprehensive.
        TXT;

        return new LlmRequest(config('llm.system_prompt'), $prompt);
    }

    public static function draft(string $topic, string $keyword, int $targetWords): LlmRequest
    {
        $prompt = <<<TXT
        Write a complete, original SEO article draft in markdown.

        Topic: {$topic}
        Focus keyword: {$keyword}
        Target length: {$targetWords} words

        Requirements:
        - One H1 title that includes the focus keyword naturally.
        - Use the focus keyword in the introduction and a few H2 headings, without stuffing.
        - Clear heading hierarchy (H2/H3), short readable paragraphs.
        - Write for humans first; aim close to the target length.
        TXT;

        return new LlmRequest(config('llm.system_prompt'), $prompt, maxTokens: 4000);
    }
}
