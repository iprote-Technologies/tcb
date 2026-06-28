<?php

declare(strict_types=1);

namespace Iprote\TcbCms\Services;

use Iprote\TcbCms\Enums\AccountType;
use Iprote\TcbCms\Exceptions\AccountNotFoundException;
use Iprote\TcbCms\Exceptions\BranchNotFoundException;
use Iprote\TcbCms\Models\BankAccount;
use Iprote\TcbCms\Models\Branch;

class AccountResolverService
{
    public function resolveBranch(string|int|Branch $branch): Branch
    {
        if ($branch instanceof Branch) {
            return $branch;
        }

        $resolved = Branch::query()
            ->where(is_numeric($branch) ? 'id' : 'code', $branch)
            ->where('status', 'active')
            ->first();

        if (! $resolved) {
            throw new BranchNotFoundException("Branch [{$branch}] not found or inactive.");
        }

        return $resolved;
    }

    public function resolve(
        string|int|Branch $branch,
        AccountType $accountType,
        ?BankAccount $override = null,
    ): BankAccount {
        if ($override) {
            return $override;
        }

        $branchModel = $this->resolveBranch($branch);

        $account = BankAccount::query()
            ->where('branch_id', $branchModel->id)
            ->where('account_type', $accountType->value)
            ->where('status', 'active')
            ->where('is_default', true)
            ->first();

        if (! $account) {
            $account = BankAccount::query()
                ->where('branch_id', $branchModel->id)
                ->where('account_type', $accountType->value)
                ->where('status', 'active')
                ->first();
        }

        if (! $account) {
            throw new AccountNotFoundException(
                "No {$accountType->value} account found for branch [{$branchModel->code}]."
            );
        }

        return $account;
    }

    public function collectionAccount(string|int|Branch $branch, ?BankAccount $override = null): BankAccount
    {
        return $this->resolve($branch, AccountType::Collection, $override);
    }

    public function disbursementAccount(string|int|Branch $branch, ?BankAccount $override = null): BankAccount
    {
        return $this->resolve($branch, AccountType::Disbursement, $override);
    }

    public function loanCollectionAccount(string|int|Branch $branch, ?BankAccount $override = null): BankAccount
    {
        return $this->resolve($branch, AccountType::LoanCollection, $override);
    }
}
