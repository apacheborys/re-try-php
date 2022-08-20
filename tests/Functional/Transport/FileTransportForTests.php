<?php
declare(strict_types=1);

namespace ApacheBorys\Retry\Tests\Functional\Transport;

use ApacheBorys\Retry\Entity\Config;
use ApacheBorys\Retry\Entity\Message;
use ApacheBorys\Retry\Interfaces\Transport;

class FileTransportForTests implements Transport
{
    private $fp;

    public function __construct(string $fileName)
    {
        $this->fp = fopen($fileName, 'a');
    }

    public function send(Message $message): bool
    {
        return (bool) fputs($this->fp,  $message . PHP_EOL);
    }

    public function howManyTriesWasBefore(\Throwable $exception, Config $config): int
    {
        return 1;
    }
}
