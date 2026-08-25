# Telegram Notify for Flarum

![Flarum](https://img.shields.io/badge/Flarum-%5E2.0-26A5E4)
![PHP](https://img.shields.io/badge/PHP-%5E8.3-777BB4)
![License](https://img.shields.io/badge/license-MIT-green)
[![Latest Stable Version](https://img.shields.io/packagist/v/stezkoy/flarum-telegram-notify.svg)](https://packagist.org/packages/stezkoy/flarum-telegram-notify)
[![Total Downloads](https://img.shields.io/packagist/dt/stezkoy/flarum-telegram-notify.svg)](https://packagist.org/packages/stezkoy/flarum-telegram-notify)

[Русская версия](README_RU.md)

Sends Telegram notifications about new discussions and replies on your Flarum forum.

![Extension settings](https://raw.githubusercontent.com/Stezkoy/flarum-telegram-notify/refs/heads/main/img/prew.png)

## Features

- Notifications for new discussions and replies
- Customizable message templates with placeholders
- Telegram HTML formatting and emoji
- Topics in forum-style groups (optional)
- Proxy support

## Installation

```bash
composer require stezkoy/flarum-telegram-notify
php flarum cache:clear
```

## Requirements

- PHP ^8.3
- Flarum ^2.0

## Setup

Create a bot via [@BotFather](https://t.me/BotFather), add it as admin to your group/channel, then open **Admin → Extensions → Telegram Notify** and configure the connection and message templates.

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
💬 <b>{title}</b>
👤 {author}
{excerpt}
{url}
```

**Title as a link**

```text
🆕 <a href="{url}"><b>{title}</b></a>
👤 {author}
{excerpt}
```

**Labeled link**

```text
💬 <b>{title}</b>
👤 {author}
{excerpt}

👉 <a href="{url}">Go to topic</a>
```

## License

MIT · [Stezkoy](https://github.com/Stezkoy)
