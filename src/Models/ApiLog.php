<?php

declare(strict_types=1);

namespace Iprote\TcbCms\Models;

use Illuminate\Database\Eloquent\Model;

class ApiLog extends Model
{
    protected $fillable = [
        'endpoint',
        'method',
        'branch_code',
        'request_payload',
        'response_payload',
        'http_status',
        'response_time_ms',
        'success',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'request_payload' => 'array',
            'response_payload' => 'array',
            'success' => 'boolean',
        ];
    }

    public function getTable(): string
    {
        return config('tcb.tables.api_logs', 'tcb_api_logs');
    }
}
