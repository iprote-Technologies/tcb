<?php

declare(strict_types=1);

namespace Iprote\TcbCms\Tests\Feature;

use Iprote\TcbCms\Enums\AccountType;
use Iprote\TcbCms\Enums\ReferenceStatus;
use Iprote\TcbCms\Models\BankAccount;
use Iprote\TcbCms\Models\Branch;
use Iprote\TcbCms\Models\ReferenceNumber;
use Iprote\TcbCms\Tests\TestCase;

class WebhookTest extends TestCase
{
    public function test_webhook_processes_valid_payment(): void
    {
        $branch = Branch::query()->create([
            'name' => 'Main',
            'code' => 'MAIN',
            'status' => 'active',
        ]);

        $account = BankAccount::query()->create([
            'branch_id' => $branch->id,
            'account_name' => 'Collection',
            'account_number' => '173200000001',
            'profile_id' => '173200000001',
            'account_type' => AccountType::Collection->value,
            'is_default' => true,
            'status' => 'active',
        ]);

        ReferenceNumber::query()->create([
            'branch_id' => $branch->id,
            'bank_account_id' => $account->id,
            'reference' => '999ABC123456',
            'status' => ReferenceStatus::Active,
        ]);

        $payload = [
            'status' => 0,
            'statusDesc' => 'Success',
            'param' => [
                'transaction_id' => 'P16092429560',
                'reference' => '999ABC123456',
                'amount' => 350000,
                'currency' => 'TZS',
                'transaction_date' => '2020-12-29T15:05:21.000+0300',
                'phone' => 'John Doe',
                'description' => 'TUITION FEE',
                'account_no' => '240XXXXXXXXX',
                'charge' => 0.0,
            ],
        ];

        $response = $this->postJson('/webhooks/tcb', $payload);

        $response->assertOk()
            ->assertJson(['Status' => 0]);

        $this->assertDatabaseHas(config('tcb.tables.transactions'), [
            'transaction_id' => 'P16092429560',
            'reference' => '999ABC123456',
        ]);
    }

    public function test_webhook_returns_invalid_for_unknown_reference(): void
    {
        $payload = [
            'status' => 0,
            'statusDesc' => 'Success',
            'param' => [
                'transaction_id' => 'P999',
                'reference' => 'UNKNOWN',
                'amount' => 1000,
                'currency' => 'TZS',
            ],
        ];

        $response = $this->postJson('/webhooks/tcb', $payload);

        $response->assertOk()
            ->assertJson(['Status' => 1, 'Message' => 'Invalid Reference Number']);
    }
}
