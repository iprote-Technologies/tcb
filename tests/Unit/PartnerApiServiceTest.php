<?php

declare(strict_types=1);

namespace Iprote\TcbCms\Tests\Unit;

use Iprote\TcbCms\Services\PartnerApiService;
use Iprote\TcbCms\Tests\TestCase;
use Illuminate\Support\Facades\Http;

class PartnerApiServiceTest extends TestCase
{
    public function test_it_authenticates_partner(): void
    {
        Http::fake([
            '*/tcb/partners/auth/authenticate' => Http::response([
                'access_token' => 'abc123',
                'token_type' => 'Bearer',
                'status' => 'success',
            ], 200),
        ]);

        config()->set('tcb.client_id', 'partner-xyz');
        config()->set('tcb.client_secret', 'secret-xyz');

        $response = app(PartnerApiService::class)->authenticate();

        $this->assertSame('abc123', $response['access_token']);
    }

    public function test_it_calls_account_lookup(): void
    {
        Http::fake([
            '*/tcb/partners/tips-lookup' => Http::response([
                'responseCode' => '0',
                'message' => 'success',
                'beneficiaryName' => 'LUCAS VICTOR MCHANA',
            ], 200),
        ]);

        $response = app(PartnerApiService::class)->accountLookup([
            'accountNo' => '110210001001',
            'institutionCode' => '048',
        ]);

        $this->assertSame('0', $response['responseCode']);
    }

    public function test_it_calls_transaction_inquiry(): void
    {
        Http::fake([
            '*/tcb/partners/tqs*' => Http::response([
                'responseCode' => '0',
                'message' => 'COMMITTED',
                'reference' => 'DER561776ER',
            ], 200),
        ]);

        $response = app(PartnerApiService::class)->transactionInquiry('DER561776ER');

        $this->assertSame('COMMITTED', $response['message']);
    }
}
