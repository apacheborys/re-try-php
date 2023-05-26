<?php
declare(strict_types=1);

namespace ApacheBorys\Retry\Traits;

use Psr\Log\LoggerInterface;

trait LogWrapper
{
    protected function sendLogRecord(string $level, string $string): void
    {
        if ($this->logger instanceof LoggerInterface) {
            $this->logger->{$level}('Retry lib: ' . $string);
        }
    }
}
