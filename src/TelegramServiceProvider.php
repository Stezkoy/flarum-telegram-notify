<?php

namespace Stezkoy\NouiTeleNotify;

use Flarum\Foundation\AbstractServiceProvider;

class TelegramServiceProvider extends AbstractServiceProvider
{
    public function register(): void
    {
        $this->container->singleton(TelegramConfig::class);
        $this->container->singleton(TelegramNotifier::class);
    }
}