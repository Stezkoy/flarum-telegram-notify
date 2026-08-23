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
        ->default('stezkoy-telegram-notify.new_post_template', MessageTemplates::NEW_POST),

    new Extend\Locales(__DIR__ . '/locale'),
];
