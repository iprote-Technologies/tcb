<?php

declare(strict_types=1);

namespace Iprote\TcbCms;

use Iprote\TcbCms\Builders\TcbBranchBuilder;
use Iprote\TcbCms\Models\Branch;
use Iprote\TcbCms\Models\PaymentTransaction;
use Iprote\TcbCms\Models\ReferenceNumber;
use Iprote\TcbCms\Models\ReconciliationLog;
use Iprote\TcbCms\Services\AccountResolverService;
use Iprote\TcbCms\Services\BranchService;
use Iprote\TcbCms\Services\DisbursementService;
use Iprote\TcbCms\Services\PaymentService;
use Iprote\TcbCms\Services\ReconciliationService;
use Iprote\TcbCms\Services\ReferenceService;

class TCBManager
{
    public function __construct(
        protected ReferenceService $referenceService,
        protected PaymentService $paymentService,
        protected ReconciliationService $reconciliationService,
        protected DisbursementService $disbursementService,
        protected BranchService $branchService,
        protected AccountResolverService $accountResolver,
    ) {}

    public function branch(string|int|Branch $branch): TcbBranchBuilder
    {
        $branchModel = $this->accountResolver->resolveBranch($branch);

        return new TcbBranchBuilder(
            $branchModel,
            $this->accountResolver,
            $this->referenceService,
            $this->disbursementService,
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createReference(string|int|Branch $branch, array $data, bool $queued = false): ReferenceNumber
    {
        return $this->referenceService->create($branch, $data, queued: $queued);
    }

    public function cancelReference(string|int|Branch $branch, string $reference, bool $queued = false): ReferenceNumber
    {
        return $this->referenceService->cancel($branch, $reference, queued: $queued);
    }

    public function reconciliation(
        string|int|Branch $branch,
        string $startDate,
        ?string $endDate = null,
        bool $queued = false,
    ): ReconciliationLog {
        return $this->reconciliationService->dateRange(
            $branch,
            $startDate,
            $endDate ?? $startDate,
            $queued,
        );
    }

    public function verifyPayment(string $reference): PaymentTransaction
    {
        return $this->paymentService->verifyPayment($reference);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function disburseLoan(string|int|Branch $branch, array $data): PaymentTransaction
    {
        return $this->disbursementService->disburseLoan($branch, $data);
    }

    public function branches(): BranchService
    {
        return $this->branchService;
    }

    public function account(): AccountResolverService
    {
        return $this->accountResolver;
    }

    public function references(): ReferenceService
    {
        return $this->referenceService;
    }

    public function payments(): PaymentService
    {
        return $this->paymentService;
    }
}
