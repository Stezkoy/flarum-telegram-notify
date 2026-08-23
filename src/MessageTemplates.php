<?php

namespace Stezkoy\TelegramNotify;

final class MessageTemplates
{
    public const NEW_DISCUSSION = "🆕 <b>{title}</b>\n👤 {author}\n{excerpt}\n👉 {url}";

    public const NEW_POST = "💬 <b>{title}</b>\n👤 {author}\n{excerpt}\n👉 {url}";
}
