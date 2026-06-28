<?php

declare(strict_types=1);

namespace Iprote\TcbCms\Endpoints;

class ReconciliationEndpoint extends AbstractEndpoint
{
    public function name(): string
    {
        return 'reconciliation';
    }

    public function path(): string
    {
        return '/public/api/reconciliation/'.$this->apiKey();
    }

    public function baseUrl(): ?string
    {
        return config('tcb.reconciliation_url');
    }
}
