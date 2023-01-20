<?php
declare(strict_types=1);

namespace ApacheBorys\Retry\Tests\Functional\Transport;

use ApacheBorys\Retry\Entity\Config;
use ApacheBorys\Retry\Entity\Message;
use ApacheBorys\Retry\Interfaces\Transport;
use Throwable;

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
            $tmpMessage = Message::fromArray(json_decode($line, true));
            $messages[$tmpMessage->getPayload()['correlationId'] ?? ''][] = $tmpMessage;
        }

        fclose($handle);

        return isset($messages[getenv(self::ENV_VAR_FOR_CORRELATION_ID) ?? '']) ?
            count((array) $messages[getenv(self::ENV_VAR_FOR_CORRELATION_ID) ?? '']) : 0;
    }

    public function fetchUnprocessedMessages(int $batchSize = -1): ?iterable
    {
        $messages = [];

        $handle = fopen($this->fileName, 'r');

        while (($line = fgets($handle)) !== false) {
            $message = Message::fromArray(json_decode($line, true));

            if ($message->getIsProcessed()) {
                continue;
            }

            if ($batchSize === -1) {
                yield $message;
            } else {
                $messages[] = $message;
            };
        }

        fclose($handle);

        if ($batchSize !== -1) {
            return $messages;
        }
    }

    public function getMessages(int $limit = 100, int $offset = 0, bool $byStream = false): iterable
    {
        $messages = [];

        $handle = fopen($this->fileName, 'r');

        while (($line = fgets($handle)) !== false) {
            $message = Message::fromArray(json_decode($line, true));

            if ($byStream) {
                yield $message;
            } else {
                $messages[] = $message;
            };
        }

        fclose($handle);

        if (!$byStream) {
            return $messages;
        }
    }

    public function markMessageAsProcessed(Message $message): bool
    {
        $fp = fopen($this->fileName, 'r+');

        while (($line = fgets($fp)) !== false) {
            $temp = Message::fromArray(json_decode($line, true));
            $messagesFromStorage[$temp->getId()] = $temp;
        }

        $messagesFromStorage[$message->getId()]->markAsProcessed();

        fclose($fp);

        $fp = fopen($this->fileName, 'w');

        foreach($messagesFromStorage as $message) {
            fputs($fp,  $message . PHP_EOL);
        }

        fclose($fp);

        return true;
    }

    public function getNextId(Throwable $exception, Config $config): string
    {
        $fp = fopen($this->fileName, 'r+');

        $counter = 0;

        while (($line = fgets($fp)) !== false) {
            $counter++;
        }

        fclose($fp);

        return (string) $counter;
    }

    public function __destruct()
    {
        fclose($this->fp);
    }
}
