<?php

declare(strict_types=1);

namespace Iprote\TcbCms\Services;

use Iprote\TcbCms\Endpoints\ReconciliationEndpoint;
use Iprote\TcbCms\Events\ReconciliationCompleted;
use Iprote\TcbCms\Jobs\ProcessApiRequest;
use Iprote\TcbCms\Models\Branch;
use Iprote\TcbCms\Models\PaymentTransaction;
use Iprote\TcbCms\Models\ReconciliationLog;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;

class ReconciliationService
{
    public function __construct(
        protected ApiClient $apiClient,
        protected AccountResolverService $accountResolver,
        protected ReconciliationEndpoint $reconciliationEndpoint,
    ) {}

    public function daily(string|int|Branch $branch, ?string $date = null, bool $queued = false): ReconciliationLog
    {
        $date = $date ?? now()->toDateString();

        return $this->dateRange($branch, $date, $date, $queued);
    }

    public function dateRange(
        string|int|Branch $branch,
        string $startDate,
        string $endDate,
        bool $queued = false,
    ): ReconciliationLog {
        $branchModel = $this->accountResolver->resolveBranch($branch);

        $log = ReconciliationLog::query()->create([
            'branch_id' => $branchModel->id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => 'pending',
        ]);

        $payload = [
            'partnerCode' => config('tcb.partner_code'),
            'startDate' => $startDate,
            'endDate' => $endDate,
        ];

        if ($queued) {
            ProcessApiRequest::dispatch('reconciliation', $payload, $branchModel->code, $log->id);

            return $log;
        }

        $response = $this->apiClient->post($this->reconciliationEndpoint, $payload, $branchModel->code);

        return $this->processReconciliationResponse($log, $response, $branchModel);
    }

    /**
     * @param  array<string, mixed>|list<array<string, mixed>>  $response
     */
    public function processReconciliationResponse(
        ReconciliationLog $log,
        array $response,
        Branch $branch,
    ): ReconciliationLog {
        $transactions = isset($response[0]) && is_array($response[0]) ? $response : [$response];
        $totalAmount = 0;
        $count = 0;

        foreach ($transactions as $item) {
            if (! isset($item['reference'])) {
                continue;
            }

            $amount = (float) ($item['amount'] ?? 0);
            $totalAmount += $amount;
            $count++;

            PaymentTransaction::query()->updateOrCreate(
                ['transaction_id' => $item['ptid'] ?? null, 'reference' => $item['reference']],
                [
                    'branch_id' => $branch->id,
                    'receipt_no' => $item['receipt_no'] ?? null,
                    'amount' => $amount,
                    'currency' => 'TZS',
                    'payment_type' => $item['payment_type'] ?? null,
                    'account_no' => $item['acct_no'] ?? null,
                    'description' => $item['details'] ?? null,
                    'status' => 'completed',
                    'transaction_type' => 'reconciliation',
                    'transaction_date' => $item['trans_date'] ?? now(),
                    'raw_payload' => $item,
                ]
            );
        }

        $log->update([
            'transaction_count' => $count,
            'total_amount' => $totalAmount,
            'status' => 'completed',
            'api_response' => $response,
        ]);

        Event::dispatch(new ReconciliationCompleted($log->fresh(), $transactions));

        return $log->fresh();
    }

    public function findTransaction(string $reference): ?PaymentTransaction
    {
        return PaymentTransaction::query()->where('reference', $reference)->latest()->first();
    }

    public function findByReceipt(string $receiptNo): ?PaymentTransaction
    {
        return PaymentTransaction::query()->where('receipt_no', $receiptNo)->first();
    }

    public function export(string|int|Branch $branch, string $startDate, string $endDate): Collection
    {
        $branchModel = $this->accountResolver->resolveBranch($branch);

        return PaymentTransaction::query()
            ->where('branch_id', $branchModel->id)
            ->whereDate('transaction_date', '>=', $startDate)
            ->whereDate('transaction_date', '<=', $endDate)
            ->orderBy('transaction_date')
            ->get();
    }
}
