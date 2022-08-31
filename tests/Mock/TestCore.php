<?php
declare(strict_types=1);

namespace ApacheBorys\Retry\Tests\Mock;

use ApacheBorys\Retry\ExceptionHandler;
use ApacheBorys\Retry\Entity\Config;

class TestCore extends ExceptionHandler
{
    public function getConfig(string $retryName): Config
    {
        return $this->config[$retryName];
    }
}
