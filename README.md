# NOUI Telegram Notify

[Русская версия](README_RU.md)

A Flarum v2 extension that sends notifications about new discussions and new posts to a Telegram channel or group. Configured via `config.php` — no admin panel needed.

## Features

- Notifications for new discussions and new posts with title, author, excerpt, and link
- Support for Telegram Group topics (optional `topic_id`)
- Multilingual: English, Russian, German, French, Turkish, Italian, Chinese, Polish
- HTML formatting with emoji

## Installation

```bash
composer require stezkoy/noui-tele-notify
php flarum cache:clear
```

## Configuration

Add to your forum's `config.php` under the `stezkoy-noui-tele-notify` key:

```php
'stezkoy-noui-tele-notify' => array(
    'bot_token' => '1234567890:AAF1Cc2Dd3Ee4Ff5Gg6Hh7Ii8Jj9Kk0Ll',
    'chat_id' => '-1001234567890',
    'topic_id' => 123,  // optional
),
```

| Parameter   | Required | Description                       |
| ----------- | -------- | --------------------------------- |
| `bot_token` | yes      | Telegram bot token from BotFather |
| `chat_id`   | yes      | Chat or group ID                  |
| `topic_id`  | no       | Telegram Group topic ID           |

## Requirements

PHP ^8.3, Flarum ^2.0

## Author

**Stezkoy**

## License

MIT
