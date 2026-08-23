# Telegram Notify

![Flarum](https://img.shields.io/badge/Flarum-%5E2.0-26A5E4)
![PHP](https://img.shields.io/badge/PHP-%5E8.3-777BB4)
![License](https://img.shields.io/badge/license-MIT-green)

[Русская версия](README_RU.md)

Sends a message to a Telegram channel or group when someone starts a discussion or posts a reply on your Flarum forum.

## Features

- Notifications for new discussions and replies
- Customizable message templates with placeholders
- Telegram HTML formatting and emoji
- Topics in forum-style groups (optional)

## Installation

```bash
composer require stezkoy/telegram-notify
php flarum cache:clear
```

## Setup

1. Create a bot via [@BotFather](https://t.me/BotFather) and copy the token
2. Add the bot to your group or channel as an administrator
3. Open **Admin → Extensions → Telegram Notify** and fill in:

| Setting        | Description                                        |
| -------------- | -------------------------------------------------- |
| Bot token      | Token from @BotFather                              |
| Chat ID        | `-1001234567890` for groups/channels, `@username` for public channels |
| Proxy          | Optional toggle — if `api.telegram.org` is unreachable from your server |
| Topic switch   | Enable to send messages into a specific group topic |

Messages are sent through the Flarum queue: with the default `sync` driver they go out immediately, with the `database` driver — in the background.

## Enable / disable via terminal

```bash
# enable
php flarum extension:enable stezkoy-telegram-notify

# disable
php flarum extension:disable stezkoy-telegram-notify

# apply changes
php flarum cache:clear
```

## Message templates

Two textareas define how notifications look: one for new discussions, one for replies.

| Placeholder  | Value                                  |
| ------------ | -------------------------------------- |
| `{title}`    | Discussion title                       |
| `{author}`   | Author name                            |
| `{excerpt}`  | First ~200 characters of the post text |
| `{url}`      | Link to the discussion                 |
| `{tags}`     | Discussion tags separated by spaces    |

Telegram HTML is supported.

| Tag                         | Effect                 |
| --------------------------- | ---------------------- |
| `<b></b>`                   | bold text              |
| `<i></i>`                   | italic text            |
| `<u></u>`                   | underlined text        |
| `<s></s>`                   | strikethrough text     |
| `<a href="{url}"></a>`      | link                   |
| `<code></code>`             | monospaced text        |
| `<pre></pre>`               | code block             |
| `<blockquote></blockquote>` | quote                  |
| `<tg-spoiler></tg-spoiler>` | hidden text (spoiler)  |

### Recipes

**Default**

```text
🆕 <b>{title}</b>
👤 {author}
{excerpt}
👉 {url}
```

**Minimal**

```text
{url}
```

**Full card**

```text
🆕 <a href="{url}"><b>{title}</b></a>
🏷️ {tags}
👤 {author}

<i>{excerpt}</i>
```

**Compact reply**

```text
💬 <b>{title}</b> · 👤 {author}
{excerpt}
{url}
```

**Title as a link**

```text
🆕 <a href="{url}"><b>{title}</b></a>
👤 {author}
{excerpt}
```

**Labeled link** — instead of a raw URL

```text
💬 <b>{title}</b>
👤 {author}
{excerpt}

👉 <a href="{url}">Go to topic</a>
```

## Requirements

- PHP ^8.3
- Flarum ^2.0

## Development

Rebuild the admin panel JavaScript after changes:

```bash
cd js && npm install && npm run build
```

## Author

**Stezkoy** · MIT License
