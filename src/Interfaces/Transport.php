<?php
declare(strict_types=1);

namespace ApacheBorys\Retry\Interfaces;

use ApacheBorys\Retry\Entity\Config;
use ApacheBorys\Retry\Entity\Message;

interface Transport
{
    public function send(Message $message): bool;

    /** @return Message[]|null */
    public function fetchMessage(int $batchSize = -1): ?array;

    public function howManyTriesWasBefore(\Throwable $exception, Config $config): int;
}
