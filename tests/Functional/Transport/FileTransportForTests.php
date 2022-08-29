<?php
declare(strict_types=1);

namespace ApacheBorys\Retry\Tests\Functional\Transport;

use ApacheBorys\Retry\Entity\Config;
use ApacheBorys\Retry\Entity\Message;
use ApacheBorys\Retry\Interfaces\Transport;

class FileTransportForTests implements Transport
{
    public const ENV_VAR_FOR_CORRELATION_ID = 'RETRY_CORRELATION_ID';

    private $fp;

    private string $fileName;

    public function __construct(string $fileName)
    {
        $this->fp = fopen($fileName, 'a');
        $this->fileName = $fileName;
    }

    public function send(Message $message): bool
    {
        return (bool) fputs($this->fp,  $message . PHP_EOL);
    }

    public function howManyTriesWasBefore(\Throwable $exception, Config $config): int
    {
        $messages = [];
        $handle = fopen($this->fileName, 'r');

        while (($line = fgets($handle)) !== false) {
            $tmpMessage = Message::fromArray(json_decode($line));
            $messages[$tmpMessage->getPayload()['correlationId'] ?? ''][] = $tmpMessage;
        }

        fclose($handle);

        return count((array) $messages[getenv(self::ENV_VAR_FOR_CORRELATION_ID) ?? '']);
    }

    public function fetchMessage(int $batchSize = -1): ?iterable
    {
        $messages = [];

        $handle = fopen($this->fileName, 'r');

        while (($line = fgets($handle)) !== false) {
            if ($batchSize === -1) {
                yield Message::fromArray(json_decode($line));
            } else {
                $messages[] = Message::fromArray(json_decode($line));
            }
        }

        fclose($handle);

        return $messages;
    }
}
