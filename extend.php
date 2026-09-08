<?php

use Flarum\Discussion\Event\Started;
use Flarum\Extend;
use Flarum\Post\Event\Posted;
use Stezkoy\FlarumTelegramNotify\Api\TestMessageController;
use Stezkoy\FlarumTelegramNotify\MessageTemplates;
use Stezkoy\FlarumTelegramNotify\NewDiscussionListener;
use Stezkoy\FlarumTelegramNotify\NewPostListener;
use Stezkoy\FlarumTelegramNotify\TelegramServiceProvider;

return [
    new Extend\ServiceProvider(TelegramServiceProvider::class),

    (new Extend\Event())
        ->listen(Started::class, NewDiscussionListener::class)
        ->listen(Posted::class, NewPostListener::class),

    (new Extend\Routes('api'))
        ->post('/telegram-notify/test', 'stezkoy-telegram-notify.test', TestMessageController::class),

    (new Extend\Frontend('admin'))
        ->js(__DIR__ . '/js/dist/admin.js')
        ->css(__DIR__ . '/less/admin.less'),

    (new Extend\Settings())
        ->default('stezkoy-telegram-notify.new_discussion_template', MessageTemplates::NEW_DISCUSSION)
        ->default('stezkoy-telegram-notify.new_post_template', MessageTemplates::NEW_POST)
        ->default('stezkoy-telegram-notify.enabled_tags', '[]')
        ->default('stezkoy-telegram-notify.retry_attempts', '2')
        ->default('stezkoy-telegram-notify.retry_delay', '1')
        ->default('stezkoy-telegram-notify.connect_timeout', '5')
        ->default('stezkoy-telegram-notify.timeout', '10')
        ->serializeToForum('stezkoy-telegram-notify.default_new_discussion_template', 'stezkoy-telegram-notify.new_discussion_template')
        ->serializeToForum('stezkoy-telegram-notify.default_new_post_template', 'stezkoy-telegram-notify.new_post_template'),

    new Extend\Locales(__DIR__ . '/locale'),
];
