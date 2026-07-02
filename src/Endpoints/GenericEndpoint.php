<?php

declare(strict_types=1);

namespace Iprote\TcbCms\Endpoints;

class GenericEndpoint extends AbstractEndpoint
{
    public function __construct(
        protected string $key,
        protected string $uri,
        protected string $httpMethod = 'POST',
        protected bool $json = true,
    ) {}

    public function name(): string
    {
        return $this->key;
    }

    public function path(): string
    {
        return $this->uri;
    }

    public function method(): string
    {
        return strtoupper($this->httpMethod);
    }

    public function usesJson(): bool
    {
        return $this->json;
    }
}
