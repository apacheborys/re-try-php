<?php
declare(strict_types=1);

namespace ApacheBorys\Retry\Tests\Functional\Transport;

use ApacheBorys\Retry\Entity\Config;
use ApacheBorys\Retry\Entity\Message;
use ApacheBorys\Retry\Interfaces\Transport;

class Runtime implements Transport
{
    private array $storage = [];

    public function send(Message $message): bool
    {
        $this->storage[] = $message;

        return true;
    }

    public function howManyTriesWasBefore(\Throwable $exception, Config $config): int
    {
        return 1;
    }

    public function getMessages(): array
    {
        return $this->storage;
    }
}
