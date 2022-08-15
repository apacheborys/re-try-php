<?php
declare(strict_types=1);

namespace ApacheBorys\Retry\Interfaces;

use ApacheBorys\Retry\Entity\Config;

interface Executor
{
    public function handle(): bool;

    public function compilePayload(\Throwable $exception, Config $config): array;
}
