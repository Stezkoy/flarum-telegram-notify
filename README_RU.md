# Telegram Notify for Flarum

![Flarum](https://img.shields.io/badge/Flarum-%5E2.0-26A5E4)
![PHP](https://img.shields.io/badge/PHP-%5E8.3-777BB4)
![License](https://img.shields.io/badge/license-MIT-green)
[![Latest Stable Version](https://img.shields.io/packagist/v/stezkoy/flarum-telegram-notify.svg)](https://packagist.org/packages/stezkoy/flarum-telegram-notify)
[![Total Downloads](https://img.shields.io/packagist/dt/stezkoy/flarum-telegram-notify.svg)](https://packagist.org/packages/stezkoy/flarum-telegram-notify)

[English version](README.md)

Отправляет Telegram-уведомления о новых темах и ответах на вашем форуме Flarum.

![Extension settings](https://raw.githubusercontent.com/Stezkoy/flarum-telegram-notify/refs/heads/main/img/prew.png)

## Возможности

- Уведомления о новых темах и ответах
- Настраиваемые шаблоны сообщений с подстановками
- HTML-форматирование и эмодзи Telegram
- Топики в группах с форумами (опционально)
- Поддержка прокси
- Фильтрация по тегам: уведомлять только о темах в выбранных тегах (опционально, требуется [flarum/tags](https://github.com/flarum/tags))

## Установка

```bash
composer require stezkoy/flarum-telegram-notify
php flarum cache:clear
```

## Требования

- PHP ^8.3
- Flarum ^2.0

## Настройка

Создайте бота через [@BotFather](https://t.me/BotFather), добавьте его администратором в группу/канал, затем откройте **Админ → Расширения → Telegram Notify** и настройте подключение и шаблоны сообщений.

### Фильтрация по тегам

При установленном и включённом расширении [flarum/tags](https://github.com/flarum/tags) в настройках расширения появится раздел **Теги**. Отметьте теги, чтобы уведомления приходили только о темах в этих тегах. Оставьте выбор пустым, чтобы уведомлять обо всём.

Расширение тегов необязательно: без него уведомления приходят обо всех темах и ответах.

## Обновление

Миграции применяются автоматически при первом включении расширения. При обновлении уже включённого расширения примените миграции вручную:

```bash
composer update stezkoy/flarum-telegram-notify
php flarum migrate
php flarum cache:clear
```

### Рецепты

**По умолчанию**

```text
🆕 <b>{title}</b>
👤 {author}
{excerpt}
👉 {url}
```

**Минимализм**

```text
{url}
```

**Полная карточка**

```text
🆕 <a href="{url}"><b>{title}</b></a>
🏷️ {tags}
👤 {author}

<i>{excerpt}</i>
```

**Компактный ответ**

```text
💬 <b>{title}</b>
👤 {author}
{excerpt}
{url}
```

**Заголовок-ссылка**

```text
🆕 <a href="{url}"><b>{title}</b></a>
👤 {author}
{excerpt}
```

**Ссылка с надписью**

```text
💬 <b>{title}</b>
👤 {author}
{excerpt}

👉 <a href="{url}">Перейти в тему</a>
```

## Лицензия

MIT · [Stezkoy](https://github.com/Stezkoy)
