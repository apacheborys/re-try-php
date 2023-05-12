<?php
declare(strict_types=1);

namespace ApacheBorys\Retry\Tests\Functional\Transport;

use ApacheBorys\Retry\Entity\Config;
use ApacheBorys\Retry\Entity\Message;
use ApacheBorys\Retry\Interfaces\Transport;
use PDO;
use Psr\Log\LoggerInterface;

class PdoTransportForTests implements Transport
{
    private PDO $pdo;

    private ?LoggerInterface $logger;

    public function __construct(string $dbLocation, ?LoggerInterface $logger = null)
    {
        $this->logger = $logger;

        $this->pdo = new PDO('sqlite:' . $dbLocation);

        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS retry_table (
    id CHAR(36),
    retry_name VARCHAR(255),
    correlation_id VARCHAR(255),
    payload TEXT,
    try_counter SMALLINT,
    is_processed TINYINT,
    should_be_executed_at DATETIME,
    executor VARCHAR(1023),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (created_at, id)
)
SQL;
        $st = $this->pdo->prepare($sql);
        $this->logSqlQuery($sql);

        if ((is_bool($st) && !$st) || !$st->execute()) {
            throw new \LogicException(
                sprintf("Can't create table for SQLite. Code: %s, additional info: %s", $this->pdo->errorCode(), serialize($this->pdo->errorInfo()))
            );
        }
    }

    public function send(Message $message): bool
    {
        $sql = <<<SQL
INSERT INTO
    `retry_table` AS e (
        `id`,
        `retry_name`,
        `correlation_id`,
        `payload`,
        `try_counter`,
        `is_processed`,
        `should_be_executed_at`,
        `executor`
    ) VALUES (
        :id,
        :retry_name,
        :correlation_id,
        :payload,
        :try_counter,
        :is_processed,
        :should_be_executed_at,
        :executor
    )
SQL;

        $id = $message->getId();
        $retryName = $message->getRetryName();
        $correlationId = $message->getCorrelationId();
        $payload = json_encode($message->getPayload());
        $tryCounter = $message->getTryCounter();
        $isProcessed = $message->getIsProcessed();
        $shouldBeExecutedAt = $message->getShouldBeExecutedAt()->format('c');
        $executor = $message->getExecutor();

        $st = $this->pdo->prepare($sql);
        $this->logSqlQuery($sql);

        $st->bindParam('id', $id);
        $st->bindParam('retry_name', $retryName);
        $st->bindParam('correlation_id', $correlationId);
        $st->bindParam('payload', $payload);
        $st->bindParam('try_counter', $tryCounter, PDO::PARAM_INT);
        $st->bindParam('is_processed', $isProcessed, PDO::PARAM_BOOL);
        $st->bindParam('should_be_executed_at', $shouldBeExecutedAt);
        $st->bindParam('executor', $executor);

        return $st->execute();
    }

    public function fetchUnprocessedMessages(int $batchSize = -1): ?iterable
    {
        $sql = <<<SQL
SELECT 
    `id`,
    `retry_name`,
    `correlation_id`,
    `payload`,
    `try_counter`,
    `is_processed`,
    `should_be_executed_at`,
    `executor`,
    `created_at`
FROM
    `retry_table` AS `e`
WHERE
    `is_processed` = 0
SQL;

        if ($batchSize > -1) {
            $sql .= ' LIMIT ' . $batchSize;
        }

        $st = $this->pdo->prepare($sql);
        $this->logSqlQuery($sql);

        if (is_bool($st) && !$st) {
            throw new \LogicException(
                sprintf("Can't fetch messages. Code: %s, additional info: %s", $this->pdo->errorCode(), serialize($this->pdo->errorInfo()))
            );
        }

        $st->execute();

        $atLeastOneRow = false;
        while ($rawMessage = $st->fetch(PDO::FETCH_ASSOC)) {
            $rawMessage['payload'] = json_decode((string) $rawMessage['payload'], true);
            $message = Message::fromArray($rawMessage);

            $atLeastOneRow = true;
            yield $message;
        }

        if (!$atLeastOneRow) {
            return null;
        }
    }

    public function getNextId(\Throwable $exception, Config $config): string
    {
        return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
            mt_rand( 0, 0xffff ),
            mt_rand( 0, 0x0fff ) | 0x4000,
            mt_rand( 0, 0x3fff ) | 0x8000,
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
        );
    }

    public function getMessages(int $limit = 100, int $offset = 0, bool $byStream = false): iterable
    {
        $sql = <<<SQL
SELECT 
    `id`,
    `retry_name`,
    `correlation_id`,
    `payload`,
    `try_counter`,
    `is_processed`,
    `should_be_executed_at`,
    `executor`,
    `created_at`,
FROM
    `retry_table` AS `e`
LIMIT
    :limit
OFFSET
    :offset
SQL;

        $st = $this->pdo->prepare($sql);
        $this->logSqlQuery($sql);

        $st->bindParam('limit', $limit, PDO::PARAM_INT);
        $st->bindParam('offset', $offset, PDO::PARAM_INT);

        $st->execute();
        $result = [];

        while ($rawMessage = $st->fetch(PDO::FETCH_ASSOC)) {
            $rawMessage['payload'] = json_decode((string) $rawMessage['payload'], true);
            $message = Message::fromArray($rawMessage);

            if ($byStream) {
                yield $message;
            } else {
                $result[] = $message;
            }
        }

        if ($byStream) {
            return $result;
        }
    }

    public function howManyTriesWasBefore(\Throwable $exception, Config $config): int
    {
        $sql = <<<SQL
SELECT MAX(`try_counter`) FROM `retry_table` AS `e` WHERE `correlation_id` = :correlation_id
SQL;
        $correlationId = $config->getExecutor()->getCorrelationId($exception, $config);

        $st = $this->pdo->prepare($sql);
        $this->logSqlQuery($sql);

        $st->bindParam('correlation_id', $correlationId);

        $st->execute();

        return (int) $st->fetchColumn();
    }

    public function markMessageAsProcessed(Message $message): bool
    {
        $sql = <<<SQL
UPDATE `retry_table` SET `is_processed` = 1 WHERE `id` = :id
SQL;

        $id = $message->getId();

        $st = $this->pdo->prepare($sql);
        $this->logSqlQuery($sql);

        $st->bindParam('id', $id);

        return $st->execute();
    }

    private function logSqlQuery(string $sql): void
    {
        if ($this->logger instanceof LoggerInterface) {
            $this->logger->debug($sql);
        }
    }
}
