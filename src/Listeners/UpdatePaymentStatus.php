<?php

declare(strict_types=1);

namespace Iprote\TcbCms\Listeners;

use Iprote\TcbCms\Events\PaymentReceived;
use Iprote\TcbCms\Enums\ReferenceStatus;

class UpdatePaymentStatus
{
    public function handlePaymentReceived(PaymentReceived $event): void
    {
        if ($event->reference && ! $event->reference->isPaid()) {
            $event->reference->update([
                'status' => ReferenceStatus::Paid,
                'paid_at' => now(),
                'amount' => $event->transaction->amount,
            ]);
        }
    }
}
