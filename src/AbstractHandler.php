<?php
declare(strict_types=1);

namespace ApacheBorys\Retry;

use ApacheBorys\Retry\Entity\Config;
use ApacheBorys\Retry\Interfaces\HandlerExceptionDeclaratorInterface;
use ApacheBorys\Retry\Traits\LogWrapper;
use Psr\Log\LoggerInterface;

abstract class AbstractHandler
{
    use LogWrapper;

    /** @var Config[] */
    protected array $config;

    protected ?LoggerInterface $logger;

    protected HandlerExceptionDeclaratorInterface $declarator;

    public function __construct(array $config, HandlerExceptionDeclaratorInterface $declarator, LoggerInterface $logger = null)
    {
        $this->logger = $logger;
        $this->declarator = $declarator;
        $this->config = $config;
    }
}
