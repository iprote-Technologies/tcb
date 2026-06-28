<?php

declare(strict_types=1);

namespace Iprote\TcbCms\Endpoints;

class CancelReferenceEndpoint extends AbstractEndpoint
{
    public function name(): string
    {
        return 'cancel_reference';
    }

    public function path(): string
    {
        return '/public/api/reference/decline/'.$this->apiKey();
    }

    public function usesJson(): bool
    {
        return true;
    }
}
