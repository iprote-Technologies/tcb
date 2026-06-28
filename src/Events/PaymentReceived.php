<?php

declare(strict_types=1);

namespace Iprote\TcbCms\Events;

use Iprote\TcbCms\Models\PaymentTransaction;
use Iprote\TcbCms\Models\ReferenceNumber;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentReceived
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public PaymentTransaction $transaction,
        public ?ReferenceNumber $reference = null,
    ) {}
}
