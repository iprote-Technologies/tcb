<?php

declare(strict_types=1);

namespace Iprote\TcbCms\Tests\Unit;

use Iprote\TcbCms\Enums\AccountType;
use Iprote\TcbCms\Exceptions\AccountNotFoundException;
use Iprote\TcbCms\Models\BankAccount;
use Iprote\TcbCms\Models\Branch;
use Iprote\TcbCms\Services\AccountResolverService;
use Iprote\TcbCms\Tests\TestCase;

class AccountResolverServiceTest extends TestCase
{
    public function test_it_resolves_default_collection_account(): void
    {
        $branch = Branch::query()->create([
            'name' => 'Main Branch',
            'code' => 'BR001',
            'currency' => 'TZS',
            'status' => 'active',
        ]);

        BankAccount::query()->create([
            'branch_id' => $branch->id,
            'account_name' => 'Collection',
            'account_number' => '173200000001',
            'profile_id' => '173200000001',
            'account_type' => AccountType::Collection->value,
            'is_default' => true,
            'status' => 'active',
        ]);

        $resolver = app(AccountResolverService::class);
        $account = $resolver->collectionAccount($branch);

        $this->assertSame('173200000001', $account->profile_id);
    }

    public function test_it_throws_when_account_missing(): void
    {
        $branch = Branch::query()->create([
            'name' => 'Empty Branch',
            'code' => 'BR002',
            'currency' => 'TZS',
            'status' => 'active',
        ]);

        $this->expectException(AccountNotFoundException::class);

        app(AccountResolverService::class)->collectionAccount($branch);
    }
}
