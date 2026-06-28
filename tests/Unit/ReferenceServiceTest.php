<?php

declare(strict_types=1);

namespace Iprote\TcbCms\Tests\Unit;

use Iprote\TcbCms\Enums\AccountType;
use Iprote\TcbCms\Models\BankAccount;
use Iprote\TcbCms\Models\Branch;
use Iprote\TcbCms\Models\ReferenceNumber;
use Iprote\TcbCms\Services\ReferenceService;
use Iprote\TcbCms\Tests\TestCase;
use Illuminate\Support\Facades\Http;

class ReferenceServiceTest extends TestCase
{
    public function test_it_creates_reference_via_api(): void
    {
        Http::fake([
            '*/public/api/reference/*' => Http::response([['Status' => 0, 'Message' => 'Success']], 200),
        ]);

        $branch = Branch::query()->create([
            'name' => 'Main',
            'code' => 'MAIN',
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

        $reference = app(ReferenceService::class)->create($branch, [
            'reference' => '999TEST123456',
            'name' => 'John Doe',
            'mobile' => '255713999934',
            'message' => 'TUITION FEE',
        ]);

        $this->assertInstanceOf(ReferenceNumber::class, $reference);
        $this->assertSame('999TEST123456', $reference->reference);
        $this->assertSame('active', $reference->status->value);

        Http::assertSentCount(1);
    }
}
