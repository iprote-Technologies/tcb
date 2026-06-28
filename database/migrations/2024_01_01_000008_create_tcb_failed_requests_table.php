<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('tcb.tables.failed_requests', 'tcb_failed_requests'), function (Blueprint $table) {
            $table->id();
            $table->string('endpoint');
            $table->string('branch_code')->nullable();
            $table->json('payload');
            $table->text('error_message');
            $table->unsignedInteger('attempts')->default(0);
            $table->timestamp('last_attempted_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index('endpoint');
            $table->index('resolved_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('tcb.tables.failed_requests', 'tcb_failed_requests'));
    }
};
