<?php

namespace Stezkoy\TelegramNotify;

use Flarum\Settings\SettingsRepositoryInterface;
use Illuminate\Contracts\Queue\Queue;
use Psr\Log\LoggerInterface;

class TelegramNotifier
{
    private const API_BASE_URL = 'https://api.telegram.org/bot';

    private const CONNECT_TIMEOUT_MS = 2000;

    private const TOTAL_TIMEOUT_MS = 5000;

    public function __construct(
        private readonly SettingsRepositoryInterface $settings,
        private readonly Queue $queue,
        private readonly LoggerInterface $logger,
    ) {}

    public function dispatch(string $message): void
    {
        if (!$this->configured()) {
            return;
        }

        $this->queue->push(new SendTelegramMessage($message));
    }

    private function configured(): bool
    {
        return (string) $this->settings->get('stezkoy-telegram-notify.bot_token') !== ''
            && (string) $this->settings->get('stezkoy-telegram-notify.chat_id') !== '';
    }

    public function send(string $message): array
    {
        $botToken = (string) $this->settings->get('stezkoy-telegram-notify.bot_token');
        $chatId = (string) $this->settings->get('stezkoy-telegram-notify.chat_id');

        $topicId = null;
        if (in_array($this->settings->get('stezkoy-telegram-notify.use_topic_id'), [true, '1', 1], true)) {
            $rawTopicId = $this->settings->get('stezkoy-telegram-notify.topic_id');
            $topicId = ($rawTopicId !== null && $rawTopicId !== '') ? (int) $rawTopicId : null;
        }

        if ($botToken === '' || $chatId === '') {
            $this->logger->warning('[stezkoy/telegram-notify] Extension is not configured: bot_token or chat_id missing.');

            return [
                'success' => false,
                'error' => 'Telegram Notify is not configured (bot_token / chat_id missing)',
            ];
        }

        $data = [
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => true,
        ];

        if ($topicId !== null) {
            $data['message_thread_id'] = $topicId;
        }

        $body = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if ($body === false) {
            return [
                'success' => false,
                'error' => 'Failed to encode message payload',
            ];
        }

        $result = $this->request(self::API_BASE_URL . $botToken . '/sendMessage', $body);

        if ($result['error'] !== null) {
            $this->logger->error('[stezkoy/telegram-notify] Request failed: ' . $result['error']);

            return [
                'success' => false,
                'error' => $result['error'],
            ];
        }

        $decoded = json_decode((string) $result['response'], true);

        if (!is_array($decoded) || !($decoded['ok'] ?? false)) {
            $description = $decoded['description'] ?? 'Unknown Telegram API error';

            $this->logger->warning('[stezkoy/telegram-notify] Telegram API error: ' . $description);

            return [
                'success' => false,
                'error' => $description,
            ];
        }

        return ['success' => true];
    }

    /**
     * @return array{response: ?string, error: ?string}
     */
    private function request(string $url, string $body): array
    {
        if (function_exists('curl_init')) {
            return $this->requestWithCurl($url, $body);
        }

        return $this->requestWithStreams($url, $body);
    }

    /**
     * @return array{response: ?string, error: ?string}
     */
    private function requestWithCurl(string $url, string $body): array
    {
        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT_MS => self::CONNECT_TIMEOUT_MS,
            CURLOPT_TIMEOUT_MS => self::TOTAL_TIMEOUT_MS,
        ]);

        $response = curl_exec($ch);

        if ($response === false) {
            $error = curl_error($ch) ?: 'Failed to connect to Telegram API';
            curl_close($ch);

            return ['response' => null, 'error' => $error];
        }

        curl_close($ch);

        return ['response' => is_string($response) ? $response : null, 'error' => null];
    }

    /**
     * @return array{response: ?string, error: ?string}
     */
    private function requestWithStreams(string $url, string $body): array
    {
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/json\r\n",
                'content' => $body,
                'timeout' => (int) ceil(self::TOTAL_TIMEOUT_MS / 1000),
                'ignore_errors' => true,
            ],
        ]);

        $response = @file_get_contents($url, false, $context);

        if ($response === false) {
            $error = error_get_last()['message'] ?? 'Failed to connect to Telegram API';

            return ['response' => null, 'error' => $error];
        }

        return ['response' => $response, 'error' => null];
    }
}
