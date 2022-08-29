<?php
declare(strict_types=1);

namespace ApacheBorys\Retry\Tests\Functional\Executor;

use ApacheBorys\Retry\Entity\Config;
use ApacheBorys\Retry\Entity\Message;
use ApacheBorys\Retry\Interfaces\Executor;
use Throwable;

class Runtime implements Executor
{
    public const ENV_VAR_FOR_CORRELATION_ID = 'RETRY_CORRELATION_ID';

    public function handle(Message $message): bool
    {
        return true;
    }

    public function compilePayload(\Throwable $exception, Config $config): array
    {
        return [];
    }

    public function getCorrelationId(Throwable $exception, Config $config): string
    {
        return 'SOME_UNIQUE_ID';
    }
}
