<?php

declare(strict_types=1);

namespace Iprote\TcbCms\Services;

use Iprote\TcbCms\Contracts\ApiClientInterface;
use Iprote\TcbCms\Contracts\EndpointInterface;
use Iprote\TcbCms\Exceptions\ApiException;
use Iprote\TcbCms\Exceptions\AuthenticationException;
use Iprote\TcbCms\Models\ApiLog;
use Iprote\TcbCms\Models\FailedRequest;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class ApiClient implements ApiClientInterface
{
    public function post(EndpointInterface $endpoint, array $payload, ?string $branchCode = null): array
    {
        return $this->request($endpoint, $payload, $branchCode, false);
    }

    public function postJson(EndpointInterface $endpoint, array $payload, ?string $branchCode = null): array
    {
        return $this->request($endpoint, $payload, $branchCode, true);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    protected function request(
        EndpointInterface $endpoint,
        array $payload,
        ?string $branchCode,
        bool $asJson,
    ): array {
        $this->enforceRateLimit();

        $baseUrl = rtrim($endpoint->baseUrl() ?? config('tcb.base_url'), '/');
        $url = $baseUrl.$endpoint->path();
        $startedAt = microtime(true);

        try {
            $request = $this->buildRequest();
            $response = $asJson || $endpoint->usesJson()
                ? $request->post($url, $payload)
                : $request->asForm()->post($url, $payload);

            $responseTimeMs = (int) ((microtime(true) - $startedAt) * 1000);
            $body = $response->json() ?? ['raw' => $response->body()];
            $parsed = $this->normalizeResponse($body);

            $this->logRequest(
                endpoint: $endpoint->name(),
                branchCode: $branchCode,
                requestPayload: $payload,
                responsePayload: $parsed,
                httpStatus: $response->status(),
                responseTimeMs: $responseTimeMs,
                success: $response->successful() && $this->isSuccessStatus($parsed),
            );

            if (! $response->successful()) {
                throw new ApiException(
                    message: $parsed['message'] ?? 'TCB API request failed.',
                    statusCode: $parsed['status'] ?? $response->status(),
                    response: $parsed,
                );
            }

            $this->validateApiStatus($parsed);

            return $parsed;
        } catch (ConnectionException $e) {
            $this->handleFailure($endpoint, $payload, $branchCode, $e->getMessage(), 2);

            throw new ApiException(
                message: 'Connection error while contacting TCB API.',
                statusCode: 2,
                previous: $e,
            );
        } catch (RequestException $e) {
            $this->handleFailure($endpoint, $payload, $branchCode, $e->getMessage());

            throw new ApiException(
                message: $e->getMessage(),
                statusCode: $e->response?->status(),
                previous: $e,
            );
        }
    }

    protected function buildRequest(): PendingRequest
    {
        $retry = config('tcb.retry', ['times' => 3, 'sleep' => 1000]);

        return Http::timeout(config('tcb.timeout', 30))
            ->withOptions(['verify' => config('tcb.verify_ssl', true)])
            ->retry($retry['times'], $retry['sleep']);
    }

    protected function enforceRateLimit(): void
    {
        if (! config('tcb.rate_limit.enabled', true)) {
            return;
        }

        $key = 'tcb-api:'.config('tcb.partner_code', 'default');
        $max = config('tcb.rate_limit.max_attempts', 60);
        $decay = config('tcb.rate_limit.decay_seconds', 60);

        if (RateLimiter::tooManyAttempts($key, $max)) {
            throw new ApiException('TCB API rate limit exceeded.');
        }

        RateLimiter::hit($key, $decay);
    }

    /**
     * @param  array<string, mixed>|list<array<string, mixed>>  $body
     * @return array<string, mixed>
     */
    protected function normalizeResponse(array $body): array
    {
        if (isset($body[0]) && is_array($body[0])) {
            return $body[0];
        }

        return $body;
    }

    /**
     * @param  array<string, mixed>  $parsed
     */
    protected function validateApiStatus(array $parsed): void
    {
        $status = $parsed['status'] ?? $parsed['Status'] ?? null;

        if ($status === null) {
            return;
        }

        $code = (int) $status;

        if ($code === 4 || $code === 5) {
            throw new AuthenticationException(
                message: $parsed['message'] ?? $parsed['Message'] ?? 'API key error.',
                statusCode: $code,
                response: $parsed,
            );
        }

        if ($code === 1) {
            throw new ApiException(
                message: $parsed['message'] ?? $parsed['Message'] ?? 'TCB API returned failure.',
                statusCode: $code,
                response: $parsed,
            );
        }
    }

    /**
     * @param  array<string, mixed>  $parsed
     */
    protected function isSuccessStatus(array $parsed): bool
    {
        $status = $parsed['status'] ?? $parsed['Status'] ?? 0;

        return (int) $status === 0;
    }

    protected function logRequest(
        string $endpoint,
        ?string $branchCode,
        array $requestPayload,
        array $responsePayload,
        ?int $httpStatus,
        int $responseTimeMs,
        bool $success,
        ?string $errorMessage = null,
    ): void {
        if (! config('tcb.logging', true)) {
            return;
        }

        Log::channel(config('tcb.log_channel', 'stack'))->info('TCB API Request', [
            'endpoint' => $endpoint,
            'branch_code' => $branchCode,
            'success' => $success,
            'response_time_ms' => $responseTimeMs,
        ]);

        ApiLog::query()->create([
            'endpoint' => $endpoint,
            'method' => 'POST',
            'branch_code' => $branchCode,
            'request_payload' => $this->redactSensitive($requestPayload),
            'response_payload' => $responsePayload,
            'http_status' => $httpStatus,
            'response_time_ms' => $responseTimeMs,
            'success' => $success,
            'error_message' => $errorMessage,
        ]);
    }

    protected function handleFailure(
        EndpointInterface $endpoint,
        array $payload,
        ?string $branchCode,
        string $message,
        ?int $statusCode = null,
    ): void {
        $this->logRequest(
            endpoint: $endpoint->name(),
            branchCode: $branchCode,
            requestPayload: $payload,
            responsePayload: [],
            httpStatus: $statusCode,
            responseTimeMs: 0,
            success: false,
            errorMessage: $message,
        );

        FailedRequest::query()->create([
            'endpoint' => $endpoint->name(),
            'branch_code' => $branchCode,
            'payload' => $this->redactSensitive($payload),
            'error_message' => $message,
            'attempts' => 1,
            'last_attempted_at' => now(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    protected function redactSensitive(array $payload): array
    {
        return $payload;
    }
}
