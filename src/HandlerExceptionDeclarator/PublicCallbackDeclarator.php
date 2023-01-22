<?php
declare(strict_types=1);

namespace ApacheBorys\Retry\HandlerExceptionDeclarator;

use ApacheBorys\Retry\Interfaces\HandlerExceptionDeclaratorInterface;

class PublicCallbackDeclarator implements HandlerExceptionDeclaratorInterface
{
    /** @var callable $callback */
    private $callback;

    public function initHandler(callable $callback): void
    {
        $this->callback = $callback;
    }

    public function getCallback(): callable
    {
        return $this->callback;
    }
}
