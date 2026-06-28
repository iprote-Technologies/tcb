<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('tcb.tables.bank_accounts', 'tcb_bank_accounts'), function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained(config('tcb.tables.branches', 'tcb_branches'))->cascadeOnDelete();
            $table->string('account_name');
            $table->string('account_number');
            $table->string('profile_id');
            $table->string('account_type');
            $table->string('currency', 3)->default('TZS');
            $table->boolean('is_default')->default(false);
            $table->string('status')->default('active');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['branch_id', 'account_type', 'is_default']);
            $table->unique(['branch_id', 'account_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('tcb.tables.bank_accounts', 'tcb_bank_accounts'));
    }
};
