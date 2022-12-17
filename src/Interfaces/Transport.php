<?php
declare(strict_types=1);

namespace ApacheBorys\Retry\Interfaces;

use ApacheBorys\Retry\Entity\{Config, Message};
use Generator;

interface Transport
{
    public function send(Message $message): bool;

    /** @return Message[]|Generator<int, Message>|null */
    public function fetchUnprocessedMessages(int $batchSize = -1): ?iterable;

    public function getNextId(\Throwable $exception, Config $config): string;

    /** @return Message[]|Generator<int, Message> */
    public function getMessages(int $limit = 100, int $offset = 0, bool $byStream = false): iterable;

    public function howManyTriesWasBefore(\Throwable $exception, Config $config): int;

    public function markMessageAsProcessed(Message $message): bool;
}
