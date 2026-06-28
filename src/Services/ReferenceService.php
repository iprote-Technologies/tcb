<?php

declare(strict_types=1);

namespace Iprote\TcbCms\Services;

use Iprote\TcbCms\Endpoints\CancelReferenceEndpoint;
use Iprote\TcbCms\Endpoints\CreateReferenceEndpoint;
use Iprote\TcbCms\Enums\AccountType;
use Iprote\TcbCms\Enums\ReferenceStatus;
use Iprote\TcbCms\Events\ReferenceCancelled;
use Iprote\TcbCms\Events\ReferenceCreated;
use Iprote\TcbCms\Exceptions\InvalidReferenceException;
use Iprote\TcbCms\Jobs\ProcessApiRequest;
use Iprote\TcbCms\Models\BankAccount;
use Iprote\TcbCms\Models\Branch;
use Iprote\TcbCms\Models\ReferenceNumber;
use Illuminate\Support\Facades\Event;

class ReferenceService
{
    public function __construct(
        protected ApiClient $apiClient,
        protected AccountResolverService $accountResolver,
        protected CreateReferenceEndpoint $createEndpoint,
        protected CancelReferenceEndpoint $cancelEndpoint,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(
        string|int|Branch $branch,
        array $data,
        ?AccountType $accountType = null,
        ?BankAccount $accountOverride = null,
        bool $queued = false,
    ): ReferenceNumber {
        $branchModel = $this->accountResolver->resolveBranch($branch);
        $account = $this->accountResolver->resolve(
            $branchModel,
            $accountType ?? AccountType::Collection,
            $accountOverride,
        );

        $reference = ReferenceNumber::query()->create([
            'branch_id' => $branchModel->id,
            'bank_account_id' => $account->id,
            'reference' => $data['reference'],
            'payer_name' => $data['name'] ?? $data['payer_name'] ?? null,
            'mobile' => $data['mobile'] ?? null,
            'message' => $data['message'] ?? null,
            'amount' => $data['amount'] ?? null,
            'currency' => $data['currency'] ?? $branchModel->currency ?? 'TZS',
            'status' => ReferenceStatus::Pending,
            'purpose' => $data['purpose'] ?? null,
            'metadata' => $data['metadata'] ?? null,
        ]);

        $payload = [
            'partnerCode' => config('tcb.partner_code'),
            'profileID' => $account->profile_id,
            'reference' => $reference->reference,
            'name' => $reference->payer_name,
            'mobile' => $reference->mobile,
            'message' => $reference->message,
        ];

        if ($queued) {
            ProcessApiRequest::dispatch('create_reference', $payload, $branchModel->code, $reference->id);

            return $reference;
        }

        $response = $this->apiClient->post($this->createEndpoint, $payload, $branchModel->code);

        $reference->update([
            'status' => ReferenceStatus::Active,
            'api_response' => $response,
        ]);

        Event::dispatch(new ReferenceCreated($reference, $response));

        return $reference->fresh();
    }

    public function cancel(
        string|int|Branch $branch,
        string $reference,
        ?BankAccount $accountOverride = null,
        bool $queued = false,
    ): ReferenceNumber {
        $referenceModel = $this->findByReference($reference);

        if (! $referenceModel) {
            throw new InvalidReferenceException("Reference [{$reference}] not found.");
        }

        if ($referenceModel->isCancelled()) {
            return $referenceModel;
        }

        $branchModel = $this->accountResolver->resolveBranch($branch);
        $account = $accountOverride ?? $referenceModel->bankAccount
            ?? $this->accountResolver->collectionAccount($branchModel);

        $payload = [
            'partnerCode' => config('tcb.partner_code'),
            'acctNo' => $account->account_number,
            'refNo' => $referenceModel->reference,
        ];

        if ($queued) {
            ProcessApiRequest::dispatch('cancel_reference', $payload, $branchModel->code, $referenceModel->id);

            return $referenceModel;
        }

        $response = $this->apiClient->postJson($this->cancelEndpoint, $payload, $branchModel->code);

        $referenceModel->update([
            'status' => ReferenceStatus::Cancelled,
            'cancelled_at' => now(),
            'api_response' => $response,
        ]);

        Event::dispatch(new ReferenceCancelled($referenceModel, $response));

        return $referenceModel->fresh();
    }

    public function findByReference(string $reference): ?ReferenceNumber
    {
        return ReferenceNumber::query()
            ->where('reference', $reference)
            ->with(['branch', 'bankAccount', 'transactions'])
            ->first();
    }

    public function validate(string $reference): bool
    {
        $model = $this->findByReference($reference);

        return $model !== null
            && ! $model->isCancelled()
            && $model->status !== ReferenceStatus::Expired;
    }

    public function history(string|int|Branch $branch): \Illuminate\Database\Eloquent\Collection
    {
        $branchModel = $this->accountResolver->resolveBranch($branch);

        return ReferenceNumber::query()
            ->where('branch_id', $branchModel->id)
            ->latest()
            ->get();
    }
}
