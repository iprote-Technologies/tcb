<?php

declare(strict_types=1);

namespace Iprote\TcbCms\Listeners;

use Iprote\TcbCms\Events\WebhookReceived;
use Illuminate\Support\Facades\Log;

class LogWebhookPayload
{
    public function handle(WebhookReceived $event): void
    {
        if (! config('tcb.logging', true)) {
            return;
        }

        Log::channel(config('tcb.log_channel', 'stack'))->info('TCB Webhook Received', [
            'webhook_log_id' => $event->webhookLog->id,
            'reference' => $event->webhookLog->reference,
            'transaction_id' => $event->webhookLog->transaction_id,
        ]);
    }
}
