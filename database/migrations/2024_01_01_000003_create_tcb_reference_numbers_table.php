<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('tcb.tables.reference_numbers', 'tcb_reference_numbers'), function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained(config('tcb.tables.branches', 'tcb_branches'));
            $table->foreignId('bank_account_id')->nullable()->constrained(config('tcb.tables.bank_accounts', 'tcb_bank_accounts'))->nullOnDelete();
            $table->string('reference')->unique();
            $table->string('payer_name')->nullable();
            $table->string('mobile')->nullable();
            $table->string('message')->nullable();
            $table->decimal('amount', 20, 2)->nullable();
            $table->string('currency', 3)->default('TZS');
            $table->string('status')->default('pending');
            $table->string('purpose')->nullable();
            $table->json('api_response')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['branch_id', 'status']);
            $table->index('reference');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('tcb.tables.reference_numbers', 'tcb_reference_numbers'));
    }
};
