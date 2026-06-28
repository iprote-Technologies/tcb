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
