# NOUI Telegram Notify

[English version](README.md)

Расширение для Flarum v2, отправляющее уведомления о новых дискуссиях и сообщениях в Telegram. Настройка через `config.php` — без панели администратора.

## Возможности

- Уведомления о новых темах и сообщениях: заголовок, автор, дата, отрывок, ссылка
- Поддержка тем Telegram-групп (опциональный `topic_id`)
- 8 языков: русский, английский, немецкий, французский, турецкий, итальянский, китайский, польский
- HTML-форматирование с эмодзи

## Установка

```bash
composer require stezkoy/noui-tele-notify
php flarum cache:clear
```

## Настройка

Добавьте в `config.php` форума в секцию `stezkoy-noui-tele-notify`:

```php
'stezkoy-noui-tele-notify' => array(
    'bot_token' => '1234567890:AAF1Cc2Dd3Ee4Ff5Gg6Hh7Ii8Jj9Kk0Ll',
    'chat_id' => '-1001234567890',
    'topic_id' => 123,  // опционально
),
```

| Параметр    | Обязательный | Описание                    |
| ----------- | ------------ | --------------------------- |
| `bot_token` | да           | Токен бота от BotFather     |
| `chat_id`   | да           | ID чата или группы          |
| `topic_id`  | нет          | ID топика в Telegram Groups |

## Требования

PHP ^8.3, Flarum ^2.0

## Автор

**Stezkoy**

## Лицензия

MIT
