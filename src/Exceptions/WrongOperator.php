<?php
declare(strict_types=1);

namespace ApacheBorys\Retry\Exceptions;

use Throwable;

class WrongOperator extends \Exception
{
    public function __construct(string $message, int $code = 0, Throwable $previous = null)
    {
        parent::__construct(sprintf("Wrong operator provided: %s", $message), $code, $previous);
    }
}
