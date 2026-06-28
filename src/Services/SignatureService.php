<?php

declare(strict_types=1);

namespace Iprote\TcbCms\Services;

use Iprote\TcbCms\Exceptions\WebhookVerificationException;
use Illuminate\Http\Request;

class SignatureService
{
    public function verify(Request $request): bool
    {
        if (! config('tcb.webhook.verify_signature', true)) {
            return true;
        }

        $secret = config('tcb.webhook_secret');

        if (empty($secret)) {
            return true;
        }

        $signature = $request->header('X-TCB-Signature')
            ?? $request->header('X-Signature')
            ?? $request->input('signature');

        if (empty($signature)) {
            throw new WebhookVerificationException('Webhook signature is missing.');
        }

        $expected = hash_hmac('sha256', $request->getContent(), $secret);

        if (! hash_equals($expected, $signature)) {
            throw new WebhookVerificationException('Webhook signature verification failed.');
        }

        return true;
    }

    public function generate(string $payload): string
    {
        $secret = config('tcb.webhook_secret', '');

        return hash_hmac('sha256', $payload, $secret);
    }
}
