<?php

declare(strict_types=1);

namespace Iprote\TcbCms\Events;

use Iprote\TcbCms\Models\ReferenceNumber;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentFailed
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public string $reference,
        public string $reason,
        public ?ReferenceNumber $referenceModel = null,
    ) {}
}
