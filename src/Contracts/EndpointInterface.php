<?php

declare(strict_types=1);

namespace Iprote\TcbCms\Contracts;

interface EndpointInterface
{
    public function name(): string;

    public function path(): string;

    public function method(): string;

    public function baseUrl(): ?string;

    public function usesJson(): bool;
}
