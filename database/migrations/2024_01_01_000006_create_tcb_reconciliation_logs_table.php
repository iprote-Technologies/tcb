<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('tcb.tables.reconciliation_logs', 'tcb_reconciliation_logs'), function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained(config('tcb.tables.branches', 'tcb_branches'))->nullOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->unsignedInteger('transaction_count')->default(0);
            $table->decimal('total_amount', 20, 2)->default(0);
            $table->string('status')->default('pending');
            $table->json('api_response')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['start_date', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('tcb.tables.reconciliation_logs', 'tcb_reconciliation_logs'));
    }
};
