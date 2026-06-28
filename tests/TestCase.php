<?php

declare(strict_types=1);

namespace Iprote\TcbCms\Tests;

use Iprote\TcbCms\TcbCmsServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [TcbCmsServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('tcb.api_key', 'test-api-key');
        $app['config']->set('tcb.partner_code', 'PART-TEST');
        $app['config']->set('tcb.base_url', 'https://partners.tcbbank.co.tz');
        $app['config']->set('tcb.webhook_secret', 'test-secret');
        $app['config']->set('tcb.logging', false);
        $app['config']->set('tcb.webhook.verify_signature', false);
        $app['config']->set('queue.default', 'sync');
        $app['config']->set('tcb.queue_connection', 'sync');
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}
