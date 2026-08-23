<?php

namespace Stezkoy\FlarumTelegramNotify;

use Illuminate\Contracts\Queue\ShouldQueue;

class SendTelegramMessage implements ShouldQueue
{
    public function __construct(
        public readonly string $message,
    ) {}

    public function handle(TelegramNotifier $notifier): void
    {
        $notifier->send($this->message);
    }
}
