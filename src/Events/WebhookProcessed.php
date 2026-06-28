<?php

declare(strict_types=1);

namespace Iprote\TcbCms\Events;

use Iprote\TcbCms\Models\WebhookLog;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WebhookProcessed
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public WebhookLog $webhookLog,
    ) {}
}
