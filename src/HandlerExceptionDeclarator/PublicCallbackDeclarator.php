<?php
declare(strict_types=1);

namespace ApacheBorys\Retry\HandlerExceptionDeclarator;

use ApacheBorys\Retry\Interfaces\HandlerExceptionDeclaratorInterface;

class PublicCallbackDeclarator implements HandlerExceptionDeclaratorInterface
{
    /** @var callable|null $callback */
    private $callback = null;

    public function initHandler(callable $callback): void
    {
        $this->callback = $callback;
    }

    public function getCallback(): callable
    {
        if (is_null($this->callback)) {
            throw new \LogicException('Callback still not initialized. Please call `getCallback` method after `initHandler`');
        }

        return $this->callback;
    }
}
