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
use Iprote\TcbCms\Services\PartnerApiService;
use Iprote\TcbCms\Services\ReconciliationService;
use Iprote\TcbCms\Services\ReferenceService;

class TCBManager
{
    public function __construct(
        protected ReferenceService $referenceService,
        protected PaymentService $paymentService,
        protected PartnerApiService $partnerApiService,
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

    public function partners(): PartnerApiService
    {
        return $this->partnerApiService;
    }

    /**
     * @return array<string, mixed>
     */
    public function authenticate(?string $clientId = null, ?string $clientSecret = null): array
    {
        return $this->partnerApiService->authenticate($clientId, $clientSecret);
    }

    /**
     * @return array<string, mixed>
     */
    public function botFsps(): array
    {
        return $this->partnerApiService->listFsps();
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function accountLookup(array $payload): array
    {
        return $this->partnerApiService->accountLookup($payload);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function aggregatorPayment(array $payload): array
    {
        return $this->partnerApiService->aggregatorPayment($payload);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function utilityPayment(array $payload): array
    {
        return $this->partnerApiService->utilityPayment($payload);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function airtimePayment(array $payload): array
    {
        return $this->partnerApiService->airtimePayment($payload);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function gepgLookup(array $payload): array
    {
        return $this->partnerApiService->gepgLookup($payload);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function gepgPayment(array $payload): array
    {
        return $this->partnerApiService->gepgPayment($payload);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function deposit(array $payload): array
    {
        return $this->partnerApiService->deposit($payload);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function withdrawal(array $payload): array
    {
        return $this->partnerApiService->withdrawal($payload);
    }

    /**
     * @return array<string, mixed>
     */
    public function transactionInquiry(string $reference): array
    {
        return $this->partnerApiService->transactionInquiry($reference);
    }
}
