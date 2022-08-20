<?php
declare(strict_types=1);

namespace ApacheBorys\Retry\Tests\Mock;

use ApacheBorys\Retry\Core;
use ApacheBorys\Retry\Entity\Config;

class TestCore extends Core
{
    public function getConfig(string $retryName): Config
    {
        return $this->config[$retryName];
    }
}
