<?php

declare(strict_types=1);

namespace Iprote\TcbCms\Models;

use Iprote\TcbCms\Enums\WebhookStatus;
use Illuminate\Database\Eloquent\Model;

class WebhookLog extends Model
{
    protected $fillable = [
        'event_id',
        'reference',
        'transaction_id',
        'status',
        'payload',
        'headers',
        'signature',
        'signature_valid',
        'attempts',
        'error_message',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => WebhookStatus::class,
            'payload' => 'array',
            'headers' => 'array',
            'signature_valid' => 'boolean',
            'processed_at' => 'datetime',
        ];
    }

    public function getTable(): string
    {
        return config('tcb.tables.webhook_logs', 'tcb_webhook_logs');
    }
}
