<?php

namespace Stezkoy\TelegramNotify;

final class TemplateRenderer
{
    /**
     * @param array<string, string> $placeholders
     */
    public static function render(string $template, array $placeholders): string
    {
        return strtr($template, $placeholders);
    }

    public static function escape(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * Returns the discussion tags as "#tag1 #tag2", or an empty string
     * when the tags extension is not available or the discussion has none.
     */
    public static function discussionTags($discussion): string
    {
        try {
            $tags = $discussion->tags;

            if ($tags !== null && $tags->isNotEmpty()) {
                return $tags
                    ->pluck('name')
                    ->map(fn ($name) => '#' . $name)
                    ->implode(' ');
            }
        } catch (\Throwable $e) {
            // tags extension not available
        }

        return '';
    }
}
