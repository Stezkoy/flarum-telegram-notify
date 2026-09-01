<?php

namespace Stezkoy\FlarumTelegramNotify;

use Flarum\Settings\SettingsRepositoryInterface;
use Illuminate\Contracts\Queue\Queue;
use Illuminate\Http\Client\Factory as HttpClient;
use Illuminate\Http\Client\RequestException;
use Psr\Log\LoggerInterface;

class TelegramNotifier
{
    private const API_BASE_URL = 'https://api.telegram.org/bot';

    private const MAX_ATTEMPTS = 2;

    public function __construct(
        private readonly SettingsRepositoryInterface $settings,
        private readonly Queue $queue,
        private readonly LoggerInterface $logger,
        private readonly HttpClient $http,
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
        if (in_array($this->settings->get('stezkoy-telegram-notify.use_topic'), [true, '1', 1], true)) {
            $rawTopicId = $this->settings->get('stezkoy-telegram-notify.topic_id');
            $topicId = ($rawTopicId !== null && $rawTopicId !== '') ? (int) $rawTopicId : null;
        }

        if ($botToken === '' || $chatId === '') {
            $this->logger->warning('[stezkoy/flarum-telegram-notify] Extension is not configured: bot_token or chat_id missing.');

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
            $this->logger->error('[stezkoy/flarum-telegram-notify] Request failed: ' . $result['error']);

            return [
                'success' => false,
                'error' => $result['error'],
            ];
        }

        $decoded = json_decode((string) $result['response'], true);

        if (!is_array($decoded) || !($decoded['ok'] ?? false)) {
            $description = $decoded['description'] ?? 'Unknown Telegram API error';

            $this->logger->warning('[stezkoy/flarum-telegram-notify] Telegram API error: ' . $description);

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
        $proxy = null;
        if (in_array($this->settings->get('stezkoy-telegram-notify.use_proxy'), [true, '1', 1], true)) {
            $raw = trim((string) $this->settings->get('stezkoy-telegram-notify.proxy'));
            $proxy = $raw !== '' ? $raw : null;
        }

        $options = [
            'connect_timeout' => 5,
            'timeout' => 10,
            'http_errors' => false,
        ];
        if ($proxy !== null) {
            $options['proxy'] = $proxy;
        }

        $lastError = null;

        for ($attempt = 1; $attempt <= self::MAX_ATTEMPTS; $attempt++) {
            try {
                $response = $this->http
                    ->withHeaders(['Content-Type' => 'application/json'])
                    ->withOptions($options)
                    ->post($url, json_decode($body, true) ?? []);

                if ($response->failed()) {
                    $status = $response->status();
                    $lastError = "Telegram API responded with HTTP {$status}";
                } else {
                    return ['response' => $response->body(), 'error' => null];
                }
            } catch (RequestException $e) {
                $lastError = $e->getMessage();
            }
        }

        return ['response' => null, 'error' => $lastError];
    }
}
