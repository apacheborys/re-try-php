<?php
declare(strict_types=1);

namespace ApacheBorys\Retry\Interfaces;

use ApacheBorys\Retry\Entity\Config;
use ApacheBorys\Retry\Entity\Message;

interface Transport
{
    public function send(Message $message): bool;

    public function howManyTriesWasBefore(\Throwable $exception, Config $config): int;
}
