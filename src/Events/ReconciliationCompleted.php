<?php

declare(strict_types=1);

namespace Iprote\TcbCms\Events;

use Iprote\TcbCms\Models\ReconciliationLog;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReconciliationCompleted
{
    use Dispatchable;
    use SerializesModels;

    /**
     * @param  list<array<string, mixed>>  $transactions
     */
    public function __construct(
        public ReconciliationLog $log,
        public array $transactions = [],
    ) {}
}
