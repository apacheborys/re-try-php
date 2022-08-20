<?php
declare(strict_types=1);

namespace ApacheBorys\Retry\Tests\Functional\Exceptions;

use Throwable;

class Mock extends \Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
