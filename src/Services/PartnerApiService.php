<?php

declare(strict_types=1);

namespace Iprote\TcbCms\Services;

use Iprote\TcbCms\Endpoints\GenericEndpoint;

class PartnerApiService
{
    public function __construct(
        protected ApiClient $apiClient,
    ) {}

    /**
     * Authenticate and return bearer token payload.
     *
     * @return array<string, mixed>
     */
    public function authenticate(?string $clientId = null, ?string $clientSecret = null): array
    {
        return $this->apiClient->postJson(
            $this->endpoint('partner_auth', '/tcb/partners/auth/authenticate'),
            [
                'client_id' => $clientId ?? config('tcb.client_id'),
                'client_secret' => $clientSecret ?? config('tcb.client_secret'),
            ],
        );
    }

    /**
     * Fetch available institutions for BOT transfers.
     *
     * @return array<string, mixed>
     */
    public function listFsps(): array
    {
        return $this->apiClient->get(
            $this->endpoint('bot_fsp_list', '/tcb/partners/tips/fsps', 'GET'),
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function accountLookup(array $payload): array
    {
        return $this->apiClient->postJson(
            $this->endpoint('account_lookup', '/tcb/partners/tips-lookup'),
            $payload,
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function aggregatorPayment(array $payload): array
    {
        return $this->apiClient->postJson(
            $this->endpoint('aggregator_payment', '/tcb/partners/aggregator/payment'),
            $payload,
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function utilityPayment(array $payload): array
    {
        return $this->apiClient->postJson(
            $this->endpoint('utility_payment', '/tcb/partners/utility/payment'),
            $payload,
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function airtimePayment(array $payload): array
    {
        return $this->apiClient->postJson(
            $this->endpoint('utility_airtime_payment', '/tcb/partners/utility/airtime'),
            $payload,
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function gepgLookup(array $payload): array
    {
        return $this->apiClient->postJson(
            $this->endpoint('gepg_lookup', '/tcb/partners/utility/gepgLookUp'),
            $payload,
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function gepgPayment(array $payload): array
    {
        return $this->apiClient->postJson(
            $this->endpoint('gepg_payment', '/tcb/partners/gepg/payment'),
            $payload,
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function deposit(array $payload): array
    {
        return $this->apiClient->postJson(
            $this->endpoint('deposit', '/tcb/partners/deposit'),
            $payload,
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function withdrawal(array $payload): array
    {
        return $this->apiClient->postJson(
            $this->endpoint('withdrawal', '/tcb/partners/withdrawal'),
            $payload,
        );
    }

    /**
     * Transaction details inquiry (TQS).
     *
     * @return array<string, mixed>
     */
    public function transactionInquiry(string $reference): array
    {
        return $this->apiClient->get(
            $this->endpoint('transaction_inquiry', '/tcb/partners/tqs', 'GET'),
            ['reference' => $reference],
        );
    }

    protected function endpoint(string $name, string $path, string $method = 'POST'): GenericEndpoint
    {
        return new GenericEndpoint($name, $path, $method, true);
    }
}
