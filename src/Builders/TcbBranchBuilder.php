<?php

declare(strict_types=1);

namespace Iprote\TcbCms\Builders;

use Iprote\TcbCms\Enums\AccountType;
use Iprote\TcbCms\Models\BankAccount;
use Iprote\TcbCms\Models\Branch;
use Iprote\TcbCms\Models\ReferenceNumber;
use Iprote\TcbCms\Services\AccountResolverService;
use Iprote\TcbCms\Services\DisbursementService;
use Iprote\TcbCms\Services\ReferenceService;

class TcbBranchBuilder
{
    protected ?BankAccount $accountOverride = null;

    protected ?AccountType $accountType = null;

    public function __construct(
        protected Branch $branch,
        protected AccountResolverService $accountResolver,
        protected ReferenceService $referenceService,
        protected DisbursementService $disbursementService,
    ) {}

    public function collectionAccount(?BankAccount $account = null): self
    {
        $this->accountType = AccountType::Collection;
        $this->accountOverride = $account;

        return $this;
    }

    public function disbursementAccount(?BankAccount $account = null): self
    {
        $this->accountType = AccountType::Disbursement;
        $this->accountOverride = $account;

        return $this;
    }

    public function loanCollectionAccount(?BankAccount $account = null): self
    {
        $this->accountType = AccountType::LoanCollection;
        $this->accountOverride = $account;

        return $this;
    }

    public function account(): BankAccount
    {
        return $this->accountResolver->resolve(
            $this->branch,
            $this->accountType ?? AccountType::Collection,
            $this->accountOverride,
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createReference(array $data, bool $queued = false): ReferenceNumber
    {
        return $this->referenceService->create(
            branch: $this->branch,
            data: $data,
            accountType: $this->accountType ?? AccountType::Collection,
            accountOverride: $this->accountOverride,
            queued: $queued,
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function disburse(array $data): \Iprote\TcbCms\Models\PaymentTransaction
    {
        return $this->disbursementService->disburseLoan(
            branch: $this->branch,
            data: $data,
            accountOverride: $this->accountOverride,
        );
    }

    public function getBranch(): Branch
    {
        return $this->branch;
    }
}
