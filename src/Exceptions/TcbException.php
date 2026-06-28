<?php

declare(strict_types=1);

namespace Iprote\TcbCms\Exceptions;

use Exception;

class TcbException extends Exception
{
    public function __construct(
        string $message = '',
        public readonly ?int $statusCode = null,
        public readonly ?array $response = null,
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
