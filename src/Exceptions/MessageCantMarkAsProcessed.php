<?php
declare(strict_types=1);

namespace ApacheBorys\Retry\Exceptions;

use Exception, Throwable;

class MessageCantMarkAsProcessed extends Exception
{
    public function __construct($code = 0, Throwable $previous = null)
    {
        parent::__construct("Can't mark as processed message. It has a risk to be processed twice", $code, $previous);
    }
}