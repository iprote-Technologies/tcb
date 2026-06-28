<?php

declare(strict_types=1);

namespace Iprote\TcbCms\Jobs;

use Iprote\TcbCms\Endpoints\CancelReferenceEndpoint;
use Iprote\TcbCms\Endpoints\CreateReferenceEndpoint;
use Iprote\TcbCms\Endpoints\ReconciliationEndpoint;
use Iprote\TcbCms\Enums\ReferenceStatus;
use Iprote\TcbCms\Events\ReferenceCancelled;
use Iprote\TcbCms\Events\ReferenceCreated;
use Iprote\TcbCms\Models\ReferenceNumber;
use Iprote\TcbCms\Models\ReconciliationLog;
use Iprote\TcbCms\Services\ApiClient;
use Iprote\TcbCms\Services\ReconciliationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Event;

class ProcessApiRequest implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries;

    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public string $endpoint,
        public array $payload,
        public ?string $branchCode = null,
        public ?int $recordId = null,
    ) {
        $this->onConnection(config('tcb.queue_connection', 'database'));
        $this->onQueue(config('tcb.queue', 'default'));
        $this->tries = config('tcb.retry.times', 3);
    }

    public function handle(
        ApiClient $apiClient,
        CreateReferenceEndpoint $createEndpoint,
        CancelReferenceEndpoint $cancelEndpoint,
        ReconciliationEndpoint $reconciliationEndpoint,
        ReconciliationService $reconciliationService,
    ): void {
        match ($this->endpoint) {
            'create_reference' => $this->handleCreateReference($apiClient, $createEndpoint),
            'cancel_reference' => $this->handleCancelReference($apiClient, $cancelEndpoint),
            'reconciliation' => $this->handleReconciliation($apiClient, $reconciliationEndpoint, $reconciliationService),
            default => null,
        };
    }

    protected function handleCreateReference(ApiClient $apiClient, CreateReferenceEndpoint $endpoint): void
    {
        $response = $apiClient->post($endpoint, $this->payload, $this->branchCode);

        if ($this->recordId) {
            $reference = ReferenceNumber::query()->find($this->recordId);

            if ($reference) {
                $reference->update([
                    'status' => ReferenceStatus::Active,
                    'api_response' => $response,
                ]);

                Event::dispatch(new ReferenceCreated($reference, $response));
            }
        }
    }

    protected function handleCancelReference(ApiClient $apiClient, CancelReferenceEndpoint $endpoint): void
    {
        $response = $apiClient->postJson($endpoint, $this->payload, $this->branchCode);

        if ($this->recordId) {
            $reference = ReferenceNumber::query()->find($this->recordId);

            if ($reference) {
                $reference->update([
                    'status' => ReferenceStatus::Cancelled,
                    'cancelled_at' => now(),
                    'api_response' => $response,
                ]);

                Event::dispatch(new ReferenceCancelled($reference, $response));
            }
        }
    }

    protected function handleReconciliation(
        ApiClient $apiClient,
        ReconciliationEndpoint $endpoint,
        ReconciliationService $reconciliationService,
    ): void {
        $response = $apiClient->post($endpoint, $this->payload, $this->branchCode);

        if ($this->recordId) {
            $log = ReconciliationLog::query()->with('branch')->find($this->recordId);

            if ($log && $log->branch) {
                $reconciliationService->processReconciliationResponse($log, $response, $log->branch);
            }
        }
    }
}
