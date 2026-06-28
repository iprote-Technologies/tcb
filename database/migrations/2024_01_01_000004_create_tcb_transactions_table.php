<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('tcb.tables.transactions', 'tcb_transactions'), function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained(config('tcb.tables.branches', 'tcb_branches'))->nullOnDelete();
            $table->foreignId('reference_number_id')->nullable()->constrained(config('tcb.tables.reference_numbers', 'tcb_reference_numbers'))->nullOnDelete();
            $table->string('transaction_id')->nullable()->unique();
            $table->string('reference')->nullable()->index();
            $table->string('receipt_no')->nullable();
            $table->decimal('amount', 20, 2);
            $table->decimal('charge', 20, 2)->default(0);
            $table->string('currency', 3)->default('TZS');
            $table->string('payment_type')->nullable();
            $table->string('account_no')->nullable();
            $table->string('phone')->nullable();
            $table->text('description')->nullable();
            $table->string('status')->default('pending');
            $table->string('transaction_type')->nullable();
            $table->timestamp('transaction_date')->nullable();
            $table->json('raw_payload')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['branch_id', 'status']);
            $table->index('transaction_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('tcb.tables.transactions', 'tcb_transactions'));
    }
};
