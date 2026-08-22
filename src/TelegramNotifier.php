<?php

namespace Stezkoy\NouiTeleNotify;

use Flarum\Foundation\Config;

class TelegramNotifier
{
    private const API_BASE_URL = 'https://api.telegram.org/bot';

    private const HTTP_TIMEOUT = 10;

    public function __construct(
        private readonly Config $config,
    ) {}

    public function send(string $message): array
    {
        $settings = $this->config->offsetGet('stezkoy-noui-tele-notify') ?? [];

        $botToken = $settings['bot_token'] ?? '';
        $chatId = $settings['chat_id'] ?? '';
        $topicId = isset($settings['topic_id']) ? (int) $settings['topic_id'] : null;

        $url = self::API_BASE_URL . $botToken . '/sendMessage';

        $data = [
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => true,
        ];

        if ($topicId !== null) {
            $data['message_thread_id'] = $topicId;
        }

        $jsonData = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if ($jsonData === false) {
            return [
                'success' => false,
                'error' => 'Failed to encode message payload',
            ];
        }

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/json\r\n",
                'content' => $jsonData,
                'timeout' => self::HTTP_TIMEOUT,
                'ignore_errors' => true,
            ],
        ]);

        $response = @file_get_contents($url, false, $context);

        if ($response === false) {
            $error = error_get_last();

            return [
                'success' => false,
                'error' => $error['message'] ?? 'Failed to connect to Telegram API',
            ];
        }

        $result = json_decode($response, true);

        if (!is_array($result) || !($result['ok'] ?? false)) {
            $description = $result['description'] ?? 'Unknown Telegram API error';

            return [
                'success' => false,
                'error' => $description,
            ];
        }

        return ['success' => true];
    }
}