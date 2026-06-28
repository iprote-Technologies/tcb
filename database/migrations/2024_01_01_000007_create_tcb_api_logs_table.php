<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('tcb.tables.api_logs', 'tcb_api_logs'), function (Blueprint $table) {
            $table->id();
            $table->string('endpoint');
            $table->string('method')->default('POST');
            $table->string('branch_code')->nullable();
            $table->json('request_payload')->nullable();
            $table->json('response_payload')->nullable();
            $table->unsignedSmallInteger('http_status')->nullable();
            $table->unsignedInteger('response_time_ms')->nullable();
            $table->boolean('success')->default(false);
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index('endpoint');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('tcb.tables.api_logs', 'tcb_api_logs'));
    }
};
