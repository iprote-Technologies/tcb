<?php

declare(strict_types=1);

namespace Iprote\TcbCms\Jobs;

use Iprote\TcbCms\Models\WebhookLog;
use Iprote\TcbCms\Services\WebhookService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessWebhook implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries;

    public function __construct(
        public int $webhookLogId,
    ) {
        $this->onConnection(config('tcb.queue_connection', 'database'));
        $this->onQueue(config('tcb.queue', 'default'));
        $this->tries = config('tcb.webhook.max_retries', 5);
    }

    public function handle(WebhookService $webhookService): void
    {
        $webhookLog = WebhookLog::query()->findOrFail($this->webhookLogId);
        $webhookService->process($webhookLog);
    }
}
