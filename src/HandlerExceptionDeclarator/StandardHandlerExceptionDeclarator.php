<?php
declare(strict_types=1);

namespace ApacheBorys\Retry\HandlerExceptionDeclarator;

use ApacheBorys\Retry\Interfaces\HandlerExceptionDeclaratorInterface;

class StandardHandlerExceptionDeclarator implements HandlerExceptionDeclaratorInterface
{
    public function initHandler(callable $callback): void
    {
        set_exception_handler($callback);
    }
}
