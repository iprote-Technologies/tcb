<?php

declare(strict_types=1);

namespace Iprote\TcbCms\Models;

use Illuminate\Database\Eloquent\Model;

class FailedRequest extends Model
{
    protected $fillable = [
        'endpoint',
        'branch_code',
        'payload',
        'error_message',
        'attempts',
        'last_attempted_at',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'last_attempted_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    public function getTable(): string
    {
        return config('tcb.tables.failed_requests', 'tcb_failed_requests');
    }
}
