<?php

declare(strict_types=1);

namespace Iprote\TcbCms\Services;

use Iprote\TcbCms\Enums\AccountType;
use Iprote\TcbCms\Enums\BranchStatus;
use Iprote\TcbCms\Models\BankAccount;
use Iprote\TcbCms\Models\Branch;
use Illuminate\Support\Collection;

class BranchService
{
    public function __construct(
        protected AccountResolverService $accountResolver,
    ) {}

    public function all(): Collection
    {
        return Branch::query()->with('bankAccounts')->get();
    }

    public function find(string|int $identifier): ?Branch
    {
        return Branch::query()
            ->where(is_numeric($identifier) ? 'id' : 'code', $identifier)
            ->with('bankAccounts')
            ->first();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Branch
    {
        return Branch::query()->create([
            'name' => $data['name'],
            'code' => $data['code'],
            'currency' => $data['currency'] ?? 'TZS',
            'status' => $data['status'] ?? BranchStatus::Active->value,
            'metadata' => $data['metadata'] ?? null,
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function addAccount(Branch $branch, array $data): BankAccount
    {
        if (! empty($data['is_default'])) {
            BankAccount::query()
                ->where('branch_id', $branch->id)
                ->where('account_type', $data['account_type'])
                ->update(['is_default' => false]);
        }

        return BankAccount::query()->create([
            'branch_id' => $branch->id,
            'account_name' => $data['account_name'],
            'account_number' => $data['account_number'],
            'profile_id' => $data['profile_id'],
            'account_type' => $data['account_type'] instanceof AccountType
                ? $data['account_type']->value
                : $data['account_type'],
            'currency' => $data['currency'] ?? $branch->currency,
            'is_default' => $data['is_default'] ?? false,
            'status' => $data['status'] ?? 'active',
            'metadata' => $data['metadata'] ?? null,
        ]);
    }

    public function setDefaultAccount(BankAccount $account): BankAccount
    {
        BankAccount::query()
            ->where('branch_id', $account->branch_id)
            ->where('account_type', $account->account_type)
            ->update(['is_default' => false]);

        $account->update(['is_default' => true]);

        return $account->fresh();
    }
}
