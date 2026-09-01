<?php

namespace Stezkoy\FlarumTelegramNotify;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendTelegramMessage implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $message,
    ) {
        $this->onQueue('telegram');
    }

    public function handle(TelegramNotifier $notifier): void
    {
        $notifier->send($this->message);
    }
}
