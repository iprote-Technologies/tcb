<?php

declare(strict_types=1);

use Iprote\TcbCms\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

Route::post(config('tcb.webhook.path', 'webhooks/tcb'), WebhookController::class)
    ->name('tcb.webhook');
