<?php

declare(strict_types=1);

namespace Iprote\TcbCms\Enums;

enum TransactionStatus: string
{
    case Pending = 'pending';
    case Completed = 'completed';
    case Failed = 'failed';
    case Reversed = 'reversed';
    case Duplicate = 'duplicate';
}
