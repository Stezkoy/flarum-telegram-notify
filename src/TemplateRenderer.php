<?php

namespace Stezkoy\FlarumTelegramNotify;

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
     * Collapses whitespace, cuts to $length characters on a word boundary.
     */
    public static function excerpt(?string $text, int $length = 200): string
    {
        $text = strip_tags((string) $text);
        $text = preg_replace('/\s+/u', ' ', trim($text)) ?? '';

        if (mb_strlen($text) <= $length) {
            return $text;
        }

        $cut = mb_substr($text, 0, $length);
        $space = mb_strrpos($cut, ' ');

        if ($space !== false && $space > (int) ($length * 0.6)) {
            $cut = mb_substr($cut, 0, $space);
        }

        return $cut . '…';
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
