<?php

declare(strict_types=1);

namespace Iprote\TcbCms\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Iprote\TcbCms\Builders\TcbBranchBuilder branch(string|int|\Iprote\TcbCms\Models\Branch $branch)
 * @method static \Iprote\TcbCms\Models\ReferenceNumber createReference(string|int|\Iprote\TcbCms\Models\Branch $branch, array $data, bool $queued = false)
 * @method static \Iprote\TcbCms\Models\ReferenceNumber cancelReference(string|int|\Iprote\TcbCms\Models\Branch $branch, string $reference, bool $queued = false)
 * @method static \Iprote\TcbCms\Models\ReconciliationLog reconciliation(string|int|\Iprote\TcbCms\Models\Branch $branch, string $startDate, ?string $endDate = null, bool $queued = false)
 * @method static \Iprote\TcbCms\Models\PaymentTransaction verifyPayment(string $reference)
 * @method static \Iprote\TcbCms\Models\PaymentTransaction disburseLoan(string|int|\Iprote\TcbCms\Models\Branch $branch, array $data)
 * @method static \Iprote\TcbCms\Services\BranchService branches()
 * @method static \Iprote\TcbCms\Services\AccountResolverService account()
 * @method static \Iprote\TcbCms\Services\ReferenceService references()
 * @method static \Iprote\TcbCms\Services\PaymentService payments()
 * @method static \Iprote\TcbCms\Services\PartnerApiService partners()
 * @method static array authenticate(?string $clientId = null, ?string $clientSecret = null)
 * @method static array botFsps()
 * @method static array accountLookup(array $payload)
 * @method static array aggregatorPayment(array $payload)
 * @method static array utilityPayment(array $payload)
 * @method static array airtimePayment(array $payload)
 * @method static array gepgLookup(array $payload)
 * @method static array gepgPayment(array $payload)
 * @method static array deposit(array $payload)
 * @method static array withdrawal(array $payload)
 * @method static array transactionInquiry(string $reference)
 *
 * @see \Iprote\TcbCms\TCBManager
 */
class TCB extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Iprote\TcbCms\TCBManager::class;
    }
}
