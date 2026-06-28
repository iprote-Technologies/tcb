<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('tcb.tables.webhook_logs', 'tcb_webhook_logs'), function (Blueprint $table) {
            $table->id();
            $table->string('event_id')->nullable()->unique();
            $table->string('reference')->nullable()->index();
            $table->string('transaction_id')->nullable()->index();
            $table->string('status')->default('received');
            $table->json('payload');
            $table->json('headers')->nullable();
            $table->string('signature')->nullable();
            $table->boolean('signature_valid')->nullable();
            $table->unsignedInteger('attempts')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('tcb.tables.webhook_logs', 'tcb_webhook_logs'));
    }
};
