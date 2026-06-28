<?php

declare(strict_types=1);

namespace Iprote\TcbCms\Models;

use Iprote\TcbCms\Enums\TransactionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentTransaction extends Model
{
    protected $fillable = [
        'branch_id',
        'reference_number_id',
        'transaction_id',
        'reference',
        'receipt_no',
        'amount',
        'charge',
        'currency',
        'payment_type',
        'account_no',
        'phone',
        'description',
        'status',
        'transaction_type',
        'transaction_date',
        'raw_payload',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'status' => TransactionStatus::class,
            'amount' => 'decimal:2',
            'charge' => 'decimal:2',
            'transaction_date' => 'datetime',
            'raw_payload' => 'array',
            'metadata' => 'array',
        ];
    }

    public function getTable(): string
    {
        return config('tcb.tables.transactions', 'tcb_transactions');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function referenceNumber(): BelongsTo
    {
        return $this->belongsTo(ReferenceNumber::class, 'reference_number_id');
    }
}
