<?php
declare(strict_types=1);

namespace ApacheBorys\Retry\Exceptions;

use Exception, Throwable;

class WrongArgument extends Exception
{
    public function __construct(string $message = "", int $code = 0, Throwable $previous = null)
    {
        parent::__construct(sprintf("Wrong argument provided: %s", $message), $code, $previous);
    }
}
