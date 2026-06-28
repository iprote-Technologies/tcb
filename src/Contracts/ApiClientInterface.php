<?php

declare(strict_types=1);

namespace Iprote\TcbCms\Contracts;

interface ApiClientInterface
{
    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function post(EndpointInterface $endpoint, array $payload, ?string $branchCode = null): array;

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function postJson(EndpointInterface $endpoint, array $payload, ?string $branchCode = null): array;
}
