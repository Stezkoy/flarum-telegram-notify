<?php

namespace Stezkoy\NouiTeleNotify;

use Flarum\Foundation\Config;

class TelegramConfig
{
    public readonly string $botToken;
    public readonly string $chatId;
    public readonly ?int $topicId;

    public function __construct(Config $config)
    {
        $settings = $config->offsetGet('stezkoy-noui-tele-notify') ?? [];

        $this->botToken = $settings['bot_token'] ?? '';
        $this->chatId = $settings['chat_id'] ?? '';
        $this->topicId = isset($settings['topic_id']) ? (int) $settings['topic_id'] : null;
    }
}