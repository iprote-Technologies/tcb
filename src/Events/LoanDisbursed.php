<?php

declare(strict_types=1);

namespace Iprote\TcbCms\Events;

use Iprote\TcbCms\Models\Branch;
use Iprote\TcbCms\Models\PaymentTransaction;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LoanDisbursed
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public PaymentTransaction $transaction,
        public Branch $branch,
    ) {}
}
