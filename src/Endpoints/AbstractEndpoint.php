<?php

declare(strict_types=1);

namespace Iprote\TcbCms\Endpoints;

use Iprote\TcbCms\Contracts\EndpointInterface;

abstract class AbstractEndpoint implements EndpointInterface
{
    public function method(): string
    {
        return 'POST';
    }

    public function baseUrl(): ?string
    {
        return null;
    }

    public function usesJson(): bool
    {
        return false;
    }

    protected function apiKey(): string
    {
        $key = config('tcb.api_key');

        if (empty($key)) {
            throw new \Iprote\TcbCms\Exceptions\AuthenticationException('TCB API key is not configured.');
        }

        return $key;
    }
}
