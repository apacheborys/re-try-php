<?php
declare(strict_types=1);

namespace ApacheBorys\Retry\Tests\Executor;

use ApacheBorys\Retry\Entity\Config;
use ApacheBorys\Retry\Interfaces\Executor;

class Runtime implements Executor
{
    public function handle(): bool
    {
        return true;
    }

    public function compilePayload(\Throwable $exception, Config $config): array
    {
        return [];
    }
}
