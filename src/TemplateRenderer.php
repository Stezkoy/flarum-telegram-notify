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
}
