<?php

declare(strict_types=1);

namespace Iprote\TcbCms\Models;

use Iprote\TcbCms\Enums\AccountType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankAccount extends Model
{
    protected $fillable = [
        'branch_id',
        'account_name',
        'account_number',
        'profile_id',
        'account_type',
        'currency',
        'is_default',
        'status',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'account_type' => AccountType::class,
            'is_default' => 'boolean',
            'metadata' => 'array',
        ];
    }

    public function getTable(): string
    {
        return config('tcb.tables.bank_accounts', 'tcb_bank_accounts');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
