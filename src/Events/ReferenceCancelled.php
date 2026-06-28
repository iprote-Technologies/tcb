<?php

declare(strict_types=1);

namespace Iprote\TcbCms\Events;

use Iprote\TcbCms\Models\ReferenceNumber;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReferenceCancelled
{
    use Dispatchable;
    use SerializesModels;

    /**
     * @param  array<string, mixed>  $response
     */
    public function __construct(
        public ReferenceNumber $reference,
        public array $response = [],
    ) {}
}
