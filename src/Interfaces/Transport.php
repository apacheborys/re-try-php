<?php
declare(strict_types=1);

namespace ApacheBorys\Retry\Interfaces;

use ApacheBorys\Retry\Entity\{Config, Message};

interface Transport
{
    public function send(Message $message): bool;

    /** @return Message[]|null */
    public function fetchUnprocessedMessages(int $batchSize = -1): ?iterable;

    public function getNextId(\Throwable $exception, Config $config): string;

    public function howManyTriesWasBefore(\Throwable $exception, Config $config): int;

    public function markMessageAsProcessed(Message $message): bool;
}
