<?php
declare(strict_types=1);

namespace ApacheBorys\Retry\Exceptions;

use Throwable;

class WrongArgument extends \Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct(sprintf("Wrong argument provided: %s", $message), $code, $previous);
    }
}