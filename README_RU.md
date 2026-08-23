# Telegram Notify

![Flarum](https://img.shields.io/badge/Flarum-%5E2.0-26A5E4)
![PHP](https://img.shields.io/badge/PHP-%5E8.3-777BB4)
![License](https://img.shields.io/badge/license-MIT-green)

[English version](README.md)

Отправляет сообщение в Telegram-канал или группу, когда на форуме Flarum появляется новая тема или ответ.

## Возможности

- Уведомления о новых темах и ответах
- Настраиваемые шаблоны сообщений с подстановками
- HTML-форматирование и эмодзи Telegram
- Топики в группах с форумами (опционально)

## Установка

```bash
composer require stezkoy/telegram-notify
php flarum cache:clear
```

## Настройка

1. Создайте бота через [@BotFather](https://t.me/BotFather) и скопируйте токен
2. Добавьте бота администратором в вашу группу или канал
3. Откройте **Админ → Расширения → Telegram Notify** и заполните:

| Параметр      | Описание                                                        |
| ------------- | --------------------------------------------------------------- |
| Токен бота    | Токен от @BotFather                                             |
| ID чата       | `-1001234567890` для групп/каналов, `@username` для публичных   |
| Прокси        | Необязательный переключатель — если `api.telegram.org` недоступен с сервера |
| Переключатель топиков | Включите, чтобы отправлять сообщения в конкретный топик |

Отправка идёт через очередь Flarum: с драйвером по умолчанию `sync` сообщения уходят сразу, с драйвером `database` — в фоне.

## Включение / отключение через терминал

```bash
# включить
php flarum extension:enable stezkoy-telegram-notify

# выключить
php flarum extension:disable stezkoy-telegram-notify

# применить изменения
php flarum cache:clear
```

## Шаблоны сообщений

Два поля определяют вид уведомлений: для новых тем и для ответов.

| Подстановка  | Значение                              |
| ------------ | ------------------------------------- |
| `{title}`    | Заголовок темы                        |
| `{author}`   | Имя автора                            |
| `{excerpt}`  | Первые ~200 символов текста сообщения |
| `{url}`      | Ссылка на тему                        |
| `{tags}`     | Теги через пробел                     |

Поддерживается HTML Telegram: `<b> <i> <u> <s> <a href=""> <code> <pre> <blockquote> <tg-spoiler>`.

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
💬 <b>{title}</b> · 👤 {author}
{excerpt}
{url}
```

**Заголовок-ссылка**

```text
🆕 <a href="{url}"><b>{title}</b></a>
👤 {author}
{excerpt}
```

**Ссылка с надписью** — вместо голого URL

```text
💬 <b>{title}</b>
👤 {author}
{excerpt}

👉 <a href="{url}">Перейти в тему</a>
```

## Требования

- PHP ^8.3
- Flarum ^2.0

## Разработка

Пересоберите JavaScript админ-панели после изменений:

```bash
cd js && npm install && npm run build
```

## Автор

**Stezkoy** · Лицензия MIT
