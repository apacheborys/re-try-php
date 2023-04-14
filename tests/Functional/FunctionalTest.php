<?php
declare(strict_types=1);

namespace ApacheBorys\Retry\Tests\Functional;

use ApacheBorys\Retry\MessageHandler;
use PDO;
use PHPUnit\Framework\TestCase;

class FunctionalTest extends TestCase
{
    private const TRANSPORT_FILE = 'tests/transport.data';
    private const CONFIG_FILE = __DIR__ . DIRECTORY_SEPARATOR . 'config.json';

    public function testExecution(): void
    {
        exec('php tests/Functional/core-test.php');

        $pdo = $this->getPdo();

        $this->assertEquals(1, $this->howManyMessagesInDb($pdo));

        $this->assertEquals(1, $this->howManyUnprocessedMessagesInDb($pdo));

        $config = json_decode(file_get_contents(self::CONFIG_FILE), true);
        $worker = new MessageHandler($config);
        $worker->processRetries(['Some\\Fake\\Class']);

        $this->assertEquals(1, $this->howManyMessagesInDb($pdo));

        $this->assertEquals(1, $this->howManyUnprocessedMessagesInDb($pdo));

        $worker->processRetries();

        $this->assertEquals(1, $this->howManyMessagesInDb($pdo));

        $this->assertEquals(0, $this->howManyUnprocessedMessagesInDb($pdo));

        unlink(self::TRANSPORT_FILE);
    }

    private function getPdo(): PDO
    {
        return new PDO('sqlite:' . self::TRANSPORT_FILE);
    }

    private function howManyMessagesInDb(PDO $pdo): int
    {
        $sql = <<<SQL
SELECT COUNT(*) FROM `retry_table`
SQL;

        return (int) $pdo->query($sql)->fetchColumn();
    }

    private function howManyUnprocessedMessagesInDb(PDO $pdo): int
    {
        $sql = <<<SQL
SELECT COUNT(*) FROM `retry_table` WHERE `is_processed` = 0
SQL;

        return (int) $pdo->query($sql)->fetchColumn();
    }

    public function __destruct()
    {
        if (file_exists(self::TRANSPORT_FILE)) {
            unlink(self::TRANSPORT_FILE);
        }
    }
}
