<?php
declare(strict_types=1);

namespace ApacheBorys\Retry\Interfaces;

interface Executor
{
    public function handle(): bool;
}
