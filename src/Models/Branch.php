<?php

declare(strict_types=1);

namespace Iprote\TcbCms\Models;

use Iprote\TcbCms\Enums\BranchStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model
{
    protected $fillable = [
        'name',
        'code',
        'currency',
        'status',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'status' => BranchStatus::class,
            'metadata' => 'array',
        ];
    }

    public function getTable(): string
    {
        return config('tcb.tables.branches', 'tcb_branches');
    }

    public function bankAccounts(): HasMany
    {
        return $this->hasMany(BankAccount::class, 'branch_id');
    }

    public function referenceNumbers(): HasMany
    {
        return $this->hasMany(ReferenceNumber::class, 'branch_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class, 'branch_id');
    }

    public function isActive(): bool
    {
        return $this->status === BranchStatus::Active;
    }
}
