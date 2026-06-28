<?php

declare(strict_types=1);

namespace Iprote\TcbCms\Models;

use Iprote\TcbCms\Enums\ReferenceStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReferenceNumber extends Model
{
    protected $fillable = [
        'branch_id',
        'bank_account_id',
        'reference',
        'payer_name',
        'mobile',
        'message',
        'amount',
        'currency',
        'status',
        'purpose',
        'api_response',
        'metadata',
        'cancelled_at',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ReferenceStatus::class,
            'amount' => 'decimal:2',
            'api_response' => 'array',
            'metadata' => 'array',
            'cancelled_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    public function getTable(): string
    {
        return config('tcb.tables.reference_numbers', 'tcb_reference_numbers');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class, 'bank_account_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class, 'reference_number_id');
    }

    public function isPaid(): bool
    {
        return $this->status === ReferenceStatus::Paid;
    }

    public function isCancelled(): bool
    {
        return $this->status === ReferenceStatus::Cancelled;
    }
}
