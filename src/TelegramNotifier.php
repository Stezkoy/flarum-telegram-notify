<?php

namespace Stezkoy\FlarumTelegramNotify;

use Flarum\Settings\SettingsRepositoryInterface;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\Queue\Queue;
use Psr\Log\LoggerInterface;

class TelegramNotifier
{
    private const API_BASE_URL = 'https://api.telegram.org/bot';

    private const MAX_ATTEMPTS = 2;

    public function __construct(
        private readonly SettingsRepositoryInterface $settings,
        private readonly Queue $queue,
        private readonly LoggerInterface $logger,
    ) {}

    private ?ClientInterface $http = null;

    public function setHttpClient(ClientInterface $http): void
    {
        $this->http = $http;
    }

    private function http(): ClientInterface
    {
        return $this->http ??= new Client();
    }

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

        if (json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) === false) {
            return [
                'success' => false,
                'error' => 'Failed to encode message payload',
            ];
        }

        $result = $this->request(self::API_BASE_URL . $botToken . '/sendMessage', $data);

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
    private function request(string $url, array $data): array
    {
        $proxy = null;
        if (in_array($this->settings->get('stezkoy-telegram-notify.use_proxy'), [true, '1', 1], true)) {
            $raw = trim((string) $this->settings->get('stezkoy-telegram-notify.proxy'));
            $proxy = $raw !== '' ? $raw : null;
        }

        $options = [
            'json' => $data,
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
                $response = $this->http()->post($url, $options);

                $status = $response->getStatusCode();

                if ($status >= 400) {
                    $lastError = $this->sanitize("Telegram API responded with HTTP {$status}", $url);
                } else {
                    return ['response' => (string) $response->getBody(), 'error' => null];
                }
            } catch (GuzzleException $e) {
                $lastError = $this->sanitize($e->getMessage(), $url);
            }
        }

        return ['response' => null, 'error' => $lastError];
    }

    /**
     * Removes the bot token segment from a message so the secret never reaches the logs.
     */
    private function sanitize(string $message, string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH);

        if (is_string($path) && preg_match('#^/bot([^/]+)/#', $path, $matches)) {
            $message = str_replace($matches[1], '***', $message);
        }

        return $message;
    }
}
