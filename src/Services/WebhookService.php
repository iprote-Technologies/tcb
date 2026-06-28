<?php

declare(strict_types=1);

namespace Iprote\TcbCms\Services;

use Iprote\TcbCms\Enums\WebhookStatus;
use Iprote\TcbCms\Events\WebhookProcessed;
use Iprote\TcbCms\Events\WebhookReceived;
use Iprote\TcbCms\Exceptions\InvalidReferenceException;
use Iprote\TcbCms\Exceptions\WebhookVerificationException;
use Iprote\TcbCms\Jobs\ProcessWebhook;
use Iprote\TcbCms\Models\WebhookLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;

class WebhookService
{
    public function __construct(
        protected SignatureService $signatureService,
        protected PaymentService $paymentService,
        protected ReferenceService $referenceService,
    ) {}

    public function handle(Request $request, bool $queued = false): array
    {
        $this->signatureService->verify($request);

        $payload = $request->all();
        $eventId = $this->resolveEventId($payload);

        if ($eventId && WebhookLog::query()->where('event_id', $eventId)->where('status', WebhookStatus::Processed)->exists()) {
            return ['Status' => 0, 'Message' => 'Already processed'];
        }

        $webhookLog = WebhookLog::query()->create([
            'event_id' => $eventId,
            'reference' => $payload['param']['reference'] ?? $payload['reference'] ?? null,
            'transaction_id' => $payload['param']['transaction_id'] ?? null,
            'status' => WebhookStatus::Received,
            'payload' => $payload,
            'headers' => $request->headers->all(),
            'signature' => $request->header('X-TCB-Signature'),
            'signature_valid' => true,
        ]);

        Event::dispatch(new WebhookReceived($webhookLog, $payload));

        if ($queued) {
            ProcessWebhook::dispatch($webhookLog->id);

            return ['Status' => 0, 'Message' => 'Accepted'];
        }

        return $this->process($webhookLog);
    }

    public function process(WebhookLog $webhookLog): array
    {
        $webhookLog->update([
            'status' => WebhookStatus::Processing,
            'attempts' => $webhookLog->attempts + 1,
        ]);

        try {
            $payload = $webhookLog->payload;
            $status = (int) ($payload['status'] ?? -1);

            if ($status !== 0) {
                throw new WebhookVerificationException($payload['statusDesc'] ?? 'Webhook reported failure.');
            }

            $param = $payload['param'] ?? [];
            $reference = $param['reference'] ?? null;

            if (! $reference) {
                throw new WebhookVerificationException('Webhook payload missing reference.');
            }

            $referenceModel = $this->referenceService->findByReference($reference);

            if (! $referenceModel) {
                $webhookLog->update([
                    'status' => WebhookStatus::Failed,
                    'error_message' => 'Invalid reference number',
                    'processed_at' => now(),
                ]);

                return ['Status' => 1, 'Message' => 'Invalid Reference Number'];
            }

            $this->paymentService->recordFromWebhook($param, $referenceModel);

            $webhookLog->update([
                'status' => WebhookStatus::Processed,
                'processed_at' => now(),
            ]);

            Event::dispatch(new WebhookProcessed($webhookLog));

            return ['Status' => 0, 'Message' => 'Success'];
        } catch (\Throwable $e) {
            $webhookLog->update([
                'status' => $webhookLog->attempts >= config('tcb.webhook.max_retries', 5)
                    ? WebhookStatus::DeadLetter
                    : WebhookStatus::Failed,
                'error_message' => $e->getMessage(),
            ]);

            if ($e instanceof InvalidReferenceException) {
                return ['Status' => 1, 'Message' => 'Invalid Reference Number'];
            }

            throw $e;
        }
    }

    public function replay(int $webhookLogId): array
    {
        $webhookLog = WebhookLog::query()->findOrFail($webhookLogId);

        return $this->process($webhookLog);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function resolveEventId(array $payload): string
    {
        $transactionId = $payload['param']['transaction_id'] ?? null;
        $reference = $payload['param']['reference'] ?? $payload['reference'] ?? null;

        if ($transactionId) {
            return 'tcb:'.$transactionId;
        }

        return 'tcb:'.Str::uuid()->toString().':'.($reference ?? 'unknown');
    }
}
