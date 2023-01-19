<?php
declare(strict_types=1);

namespace ApacheBorys\Retry\Interfaces;

interface HandlerExceptionDeclaratorInterface
{
    /**
     * Please initialise here, callback from argument as handler for exceptions. According to environment, it can be
     * different ways. For example: if we are in the framework, usually @see set_exception_handler already used by
     * core functionality. And it's not possible to replace it, because it will break framework. So, in this case, we
     * should implement event listener what can trigger that callback. That functionality, should be part of bridge
     * bundle for specific framework
     */
    public function initHandler(callable $callback): void;
}
