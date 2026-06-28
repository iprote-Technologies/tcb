<?php

declare(strict_types=1);

namespace Iprote\TcbCms\Services;

use Iprote\TcbCms\Enums\ReferenceStatus;
use Iprote\TcbCms\Enums\TransactionStatus;
use Iprote\TcbCms\Events\PaymentFailed;
use Iprote\TcbCms\Events\PaymentReceived;
use Iprote\TcbCms\Events\PaymentVerified;
use Iprote\TcbCms\Exceptions\InvalidReferenceException;
use Iprote\TcbCms\Exceptions\PaymentFailedException;
use Iprote\TcbCms\Models\PaymentTransaction;
use Iprote\TcbCms\Models\ReferenceNumber;
use Illuminate\Support\Facades\Event;

class PaymentService
{
    public function __construct(
        protected ReferenceService $referenceService,
    ) {}

    public function verifyPayment(string $reference): PaymentTransaction
    {
        $referenceModel = $this->referenceService->findByReference($reference);

        if (! $referenceModel) {
            throw new InvalidReferenceException("Reference [{$reference}] not found.");
        }

        $transaction = PaymentTransaction::query()
            ->where('reference', $reference)
            ->where('status', TransactionStatus::Completed)
            ->latest()
            ->first();

        if (! $transaction) {
            throw new PaymentFailedException("No completed payment found for reference [{$reference}].");
        }

        Event::dispatch(new PaymentVerified($transaction, $referenceModel));

        return $transaction;
    }

    public function getStatus(string $reference): ?TransactionStatus
    {
        $transaction = PaymentTransaction::query()
            ->where('reference', $reference)
            ->latest()
            ->first();

        return $transaction?->status;
    }

    public function isDuplicate(string $transactionId): bool
    {
        return PaymentTransaction::query()
            ->where('transaction_id', $transactionId)
            ->exists();
    }

    public function findByReference(string $reference): ?PaymentTransaction
    {
        return PaymentTransaction::query()
            ->where('reference', $reference)
            ->latest()
            ->first();
    }

    public function queryByBranch(int $branchId, ?string $from = null, ?string $to = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = PaymentTransaction::query()->where('branch_id', $branchId);

        if ($from) {
            $query->whereDate('transaction_date', '>=', $from);
        }

        if ($to) {
            $query->whereDate('transaction_date', '<=', $to);
        }

        return $query->latest('transaction_date')->get();
    }

    /**
     * @param  array<string, mixed>  $param
     */
    public function recordFromWebhook(array $param, ?ReferenceNumber $reference = null): PaymentTransaction
    {
        $transactionId = $param['transaction_id'] ?? null;

        if ($transactionId && $this->isDuplicate($transactionId)) {
            $existing = PaymentTransaction::query()
                ->where('transaction_id', $transactionId)
                ->first();

            $existing?->update(['status' => TransactionStatus::Duplicate]);

            return $existing;
        }

        $transaction = PaymentTransaction::query()->create([
            'branch_id' => $reference?->branch_id,
            'reference_number_id' => $reference?->id,
            'transaction_id' => $transactionId,
            'reference' => $param['reference'] ?? null,
            'amount' => $param['amount'] ?? 0,
            'charge' => $param['charge'] ?? 0,
            'currency' => $param['currency'] ?? 'TZS',
            'phone' => $param['phone'] ?? null,
            'account_no' => $param['account_no'] ?? null,
            'description' => $param['description'] ?? null,
            'status' => TransactionStatus::Completed,
            'transaction_type' => 'payment',
            'transaction_date' => $param['transaction_date'] ?? now(),
            'raw_payload' => $param,
        ]);

        if ($reference) {
            $reference->update([
                'status' => ReferenceStatus::Paid,
                'paid_at' => now(),
                'amount' => $param['amount'] ?? $reference->amount,
            ]);
        }

        Event::dispatch(new PaymentReceived($transaction, $reference));

        return $transaction;
    }

    public function markFailed(string $reference, string $reason): void
    {
        $referenceModel = $this->referenceService->findByReference($reference);

        PaymentTransaction::query()->create([
            'branch_id' => $referenceModel?->branch_id,
            'reference_number_id' => $referenceModel?->id,
            'reference' => $reference,
            'amount' => 0,
            'currency' => 'TZS',
            'status' => TransactionStatus::Failed,
            'description' => $reason,
        ]);

        Event::dispatch(new PaymentFailed($reference, $reason, $referenceModel));
    }
}
