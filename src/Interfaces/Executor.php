<?php
declare(strict_types=1);

namespace ApacheBorys\Retry\Interfaces;

use ApacheBorys\Retry\Entity\Config;
use ApacheBorys\Retry\Entity\Message;

interface Executor
{
    public function handle(Message $message): bool;

    public function compilePayload(\Throwable $exception, Config $config): array;

    public function getCorrelationId(\Throwable $exception, Config $config): string;
}
