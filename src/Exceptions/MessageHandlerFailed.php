<?php
declare(strict_types=1);

namespace ApacheBorys\Retry\Exceptions;

use Exception, Throwable;

class MessageHandlerFailed extends Exception
{
    public function __construct(int $code = 0, Throwable $previous = null)
    {
        parent::__construct("Message hanler returned failed status during message processing", $code, $previous);
    }
}
