<?php

declare(strict_types=1);

namespace Iprote\TcbCms\Enums;

enum WebhookStatus: string
{
    case Received = 'received';
    case Processing = 'processing';
    case Processed = 'processed';
    case Failed = 'failed';
    case DeadLetter = 'dead_letter';
}
