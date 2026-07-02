<?php

declare(strict_types=1);

namespace Iprote\TcbCms;

use Iprote\TcbCms\Contracts\ApiClientInterface;
use Iprote\TcbCms\Contracts\EndpointInterface;
use Iprote\TcbCms\Endpoints\CancelReferenceEndpoint;
use Iprote\TcbCms\Endpoints\CreateReferenceEndpoint;
use Iprote\TcbCms\Endpoints\ReconciliationEndpoint;
use Iprote\TcbCms\Listeners\LogWebhookPayload;
use Iprote\TcbCms\Listeners\NotifyApplication;
use Iprote\TcbCms\Listeners\UpdatePaymentStatus;
use Iprote\TcbCms\Services\AccountResolverService;
use Iprote\TcbCms\Services\ApiClient;
use Iprote\TcbCms\Services\BranchService;
use Iprote\TcbCms\Services\DisbursementService;
use Iprote\TcbCms\Services\PaymentService;
use Iprote\TcbCms\Services\PartnerApiService;
use Iprote\TcbCms\Services\ReconciliationService;
use Iprote\TcbCms\Services\ReferenceService;
use Iprote\TcbCms\Services\SignatureService;
use Iprote\TcbCms\Services\WebhookService;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class TcbCmsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/tcb.php', 'tcb');

        $this->app->singleton(ApiClientInterface::class, ApiClient::class);
        $this->app->singleton(ApiClient::class);

        $this->app->singleton(AccountResolverService::class);
        $this->app->singleton(BranchService::class);
        $this->app->singleton(ReferenceService::class);
        $this->app->singleton(PaymentService::class);
        $this->app->singleton(PartnerApiService::class);
        $this->app->singleton(WebhookService::class);
        $this->app->singleton(DisbursementService::class);
        $this->app->singleton(ReconciliationService::class);
        $this->app->singleton(SignatureService::class);

        $this->app->singleton(TCBManager::class, function ($app) {
            return new TCBManager(
                $app->make(ReferenceService::class),
                $app->make(PaymentService::class),
                $app->make(PartnerApiService::class),
                $app->make(ReconciliationService::class),
                $app->make(DisbursementService::class),
                $app->make(BranchService::class),
                $app->make(AccountResolverService::class),
            );
        });

        $this->registerEndpoints();
    }

    public function boot(): void
    {
        $this->registerPublishing();
        $this->registerMigrations();
        $this->registerRoutes();
        $this->registerEvents();
    }

    protected function registerEndpoints(): void
    {
        $endpoints = [
            CreateReferenceEndpoint::class,
            CancelReferenceEndpoint::class,
            ReconciliationEndpoint::class,
        ];

        foreach ($endpoints as $endpoint) {
            $this->app->singleton($endpoint);
            $this->app->tag([$endpoint], 'tcb.endpoints');
        }

        $this->app->bind('tcb.endpoints', function ($app) {
            return collect($app->tagged('tcb.endpoints'))
                ->mapWithKeys(fn (EndpointInterface $endpoint) => [$endpoint->name() => $endpoint]);
        });
    }

    protected function registerPublishing(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/tcb.php' => config_path('tcb.php'),
            ], 'tcb-config');

            $this->publishes([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'tcb-migrations');
        }
    }

    protected function registerMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    protected function registerRoutes(): void
    {
        if (! config('tcb.routes.enabled', true)) {
            return;
        }

        Route::group([
            'prefix' => config('tcb.routes.prefix', ''),
            'middleware' => config('tcb.webhook.middleware', ['api']),
        ], function () {
            $this->loadRoutesFrom(__DIR__.'/../routes/webhooks.php');
        });
    }

    protected function registerEvents(): void
    {
        Event::listen(
            Events\PaymentReceived::class,
            [UpdatePaymentStatus::class, 'handlePaymentReceived']
        );

        Event::listen(
            Events\WebhookReceived::class,
            [LogWebhookPayload::class, 'handle']
        );

        Event::listen(
            [
                Events\PaymentReceived::class,
                Events\PaymentFailed::class,
                Events\ReferenceCreated::class,
                Events\ReferenceCancelled::class,
                Events\DisbursementCompleted::class,
                Events\DisbursementFailed::class,
                Events\ReconciliationCompleted::class,
            ],
            [NotifyApplication::class, 'handle']
        );
    }
}
