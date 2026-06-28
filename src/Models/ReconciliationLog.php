<?php

declare(strict_types=1);

namespace Iprote\TcbCms\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReconciliationLog extends Model
{
    protected $fillable = [
        'branch_id',
        'start_date',
        'end_date',
        'transaction_count',
        'total_amount',
        'status',
        'api_response',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'total_amount' => 'decimal:2',
            'api_response' => 'array',
            'metadata' => 'array',
        ];
    }

    public function getTable(): string
    {
        return config('tcb.tables.reconciliation_logs', 'tcb_reconciliation_logs');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }
}
