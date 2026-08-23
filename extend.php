<?php

use Flarum\Discussion\Event\Started;
use Flarum\Extend;
use Flarum\Post\Event\Posted;
use Stezkoy\TelegramNotify\MessageTemplates;
use Stezkoy\TelegramNotify\NewDiscussionListener;
use Stezkoy\TelegramNotify\NewPostListener;
use Stezkoy\TelegramNotify\TelegramServiceProvider;

return [
    new Extend\ServiceProvider(TelegramServiceProvider::class),

    (new Extend\Event())
        ->listen(Started::class, NewDiscussionListener::class)
        ->listen(Posted::class, NewPostListener::class),

    (new Extend\Frontend('admin'))
        ->js(__DIR__ . '/js/dist/admin.js')
        ->css(__DIR__ . '/less/admin.less'),

    (new Extend\Settings())
        ->default('stezkoy-telegram-notify.new_discussion_template', MessageTemplates::NEW_DISCUSSION)
        ->default('stezkoy-telegram-notify.new_post_template', MessageTemplates::NEW_POST),

    new Extend\Locales(__DIR__ . '/locale'),
];
