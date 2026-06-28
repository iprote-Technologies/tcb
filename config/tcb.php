<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | TCB CMS API Credentials
    |--------------------------------------------------------------------------
    |
    | Only API credentials belong in environment configuration.
    | Bank accounts and profile IDs are stored in the database.
    |
    */

    'api_key' => env('TCB_API_KEY'),

    'partner_code' => env('TCB_PARTNER_CODE'),

    'base_url' => env('TCB_BASE_URL', 'https://partners.tcbbank.co.tz'),

    'reconciliation_url' => env('TCB_RECONCILIATION_URL', 'https://partners.tcbbank.co.tz:8444'),

    'webhook_secret' => env('TCB_WEBHOOK_SECRET'),

    'timeout' => (int) env('TCB_TIMEOUT', 30),

    'verify_ssl' => (bool) env('TCB_VERIFY_SSL', true),

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    */

    'logging' => (bool) env('TCB_LOGGING', true),

    'log_channel' => env('TCB_LOG_CHANNEL', 'stack'),

    /*
    |--------------------------------------------------------------------------
    | Queue
    |--------------------------------------------------------------------------
    */

    'queue_connection' => env('TCB_QUEUE_CONNECTION', 'database'),

    'queue' => env('TCB_QUEUE', 'default'),

    /*
    |--------------------------------------------------------------------------
    | HTTP Client
    |--------------------------------------------------------------------------
    */

    'retry' => [
        'times' => (int) env('TCB_RETRY_TIMES', 3),
        'sleep' => (int) env('TCB_RETRY_SLEEP', 1000),
    ],

    'rate_limit' => [
        'enabled' => (bool) env('TCB_RATE_LIMIT_ENABLED', true),
        'max_attempts' => (int) env('TCB_RATE_LIMIT_MAX', 60),
        'decay_seconds' => (int) env('TCB_RATE_LIMIT_DECAY', 60),
    ],

    /*
    |--------------------------------------------------------------------------
    | Webhook
    |--------------------------------------------------------------------------
    */

    'webhook' => [
        'path' => env('TCB_WEBHOOK_PATH', 'webhooks/tcb'),
        'middleware' => ['api'],
        'verify_signature' => (bool) env('TCB_WEBHOOK_VERIFY_SIGNATURE', true),
        'max_retries' => (int) env('TCB_WEBHOOK_MAX_RETRIES', 5),
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Table Names
    |--------------------------------------------------------------------------
    */

    'tables' => [
        'branches' => 'tcb_branches',
        'bank_accounts' => 'tcb_bank_accounts',
        'reference_numbers' => 'tcb_reference_numbers',
        'transactions' => 'tcb_transactions',
        'webhook_logs' => 'tcb_webhook_logs',
        'reconciliation_logs' => 'tcb_reconciliation_logs',
        'api_logs' => 'tcb_api_logs',
        'failed_requests' => 'tcb_failed_requests',
    ],

    /*
    |--------------------------------------------------------------------------
    | Route Registration
    |--------------------------------------------------------------------------
    */

    'routes' => [
        'enabled' => true,
        'prefix' => '',
    ],

];
