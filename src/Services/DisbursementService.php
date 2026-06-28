<?php

declare(strict_types=1);

namespace Iprote\TcbCms\Services;

use Iprote\TcbCms\Enums\AccountType;
use Iprote\TcbCms\Events\DisbursementCompleted;
use Iprote\TcbCms\Events\DisbursementFailed;
use Iprote\TcbCms\Events\LoanDisbursed;
use Iprote\TcbCms\Models\BankAccount;
use Iprote\TcbCms\Models\Branch;
use Iprote\TcbCms\Models\PaymentTransaction;
use Iprote\TcbCms\Enums\TransactionStatus;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;

class DisbursementService
{
    public function __construct(
        protected AccountResolverService $accountResolver,
        protected ReferenceService $referenceService,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function disburseLoan(
        string|int|Branch $branch,
        array $data,
        ?BankAccount $accountOverride = null,
    ): PaymentTransaction {
        $branchModel = $this->accountResolver->resolveBranch($branch);
        $account = $this->accountResolver->disbursementAccount($branchModel, $accountOverride);

        $transaction = PaymentTransaction::query()->create([
            'branch_id' => $branchModel->id,
            'transaction_id' => $data['transaction_id'] ?? Str::uuid()->toString(),
            'reference' => $data['reference'] ?? null,
            'amount' => $data['amount'],
            'currency' => $data['currency'] ?? $branchModel->currency,
            'status' => TransactionStatus::Pending,
            'transaction_type' => 'loan_disbursement',
            'description' => $data['description'] ?? 'Loan disbursement',
            'metadata' => array_merge($data['metadata'] ?? [], [
                'disbursement_account' => $account->account_number,
                'profile_id' => $account->profile_id,
            ]),
        ]);

        try {
            $transaction->update(['status' => TransactionStatus::Completed]);

            Event::dispatch(new DisbursementCompleted($transaction, $account));
            Event::dispatch(new LoanDisbursed($transaction, $branchModel));

            return $transaction->fresh();
        } catch (\Throwable $e) {
            $transaction->update([
                'status' => TransactionStatus::Failed,
                'description' => $e->getMessage(),
            ]);

            Event::dispatch(new DisbursementFailed($transaction, $e->getMessage()));

            throw $e;
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function collectLoanFee(
        string|int|Branch $branch,
        array $data,
        ?BankAccount $accountOverride = null,
    ): \Iprote\TcbCms\Models\ReferenceNumber {
        return $this->referenceService->create(
            branch: $branch,
            data: array_merge($data, ['purpose' => 'loan_application_fee']),
            accountType: AccountType::LoanCollection,
            accountOverride: $accountOverride,
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function collectRepayment(
        string|int|Branch $branch,
        array $data,
        ?BankAccount $accountOverride = null,
    ): \Iprote\TcbCms\Models\ReferenceNumber {
        return $this->referenceService->create(
            branch: $branch,
            data: array_merge($data, ['purpose' => 'loan_repayment']),
            accountType: AccountType::LoanCollection,
            accountOverride: $accountOverride,
        );
    }
}
