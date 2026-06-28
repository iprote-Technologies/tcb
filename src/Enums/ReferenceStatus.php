<?php

declare(strict_types=1);

namespace Iprote\TcbCms\Enums;

enum ReferenceStatus: string
{
    case Pending = 'pending';
    case Active = 'active';
    case Paid = 'paid';
    case Cancelled = 'cancelled';
    case Expired = 'expired';
    case Failed = 'failed';
}
