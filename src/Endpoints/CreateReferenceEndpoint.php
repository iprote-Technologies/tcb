<?php

declare(strict_types=1);

namespace Iprote\TcbCms\Endpoints;

class CreateReferenceEndpoint extends AbstractEndpoint
{
    public function name(): string
    {
        return 'create_reference';
    }

    public function path(): string
    {
        return '/public/api/reference/'.$this->apiKey();
    }
}
