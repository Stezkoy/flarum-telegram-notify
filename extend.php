<?php

use Flarum\Discussion\Event\Started;
use Flarum\Extend;
use Flarum\Post\Event\Posted;
use Stezkoy\NouiTeleNotify\NewDiscussionListener;
use Stezkoy\NouiTeleNotify\NewPostListener;
use Stezkoy\NouiTeleNotify\TelegramServiceProvider;

return [
    new Extend\ServiceProvider(TelegramServiceProvider::class),

    (new Extend\Event())
        ->listen(Started::class, NewDiscussionListener::class)
        ->listen(Posted::class, NewPostListener::class),

    new Extend\Locales(__DIR__ . '/locale'),
];