<?php

declare(strict_types=1);

namespace Iprote\TcbCms\Listeners;

use Illuminate\Support\Facades\Log;

class NotifyApplication
{
    public function handle(object $event): void
    {
        if (! config('tcb.logging', true)) {
            return;
        }

        Log::channel(config('tcb.log_channel', 'stack'))->debug('TCB Event Dispatched', [
            'event' => $event::class,
        ]);
    }
}
