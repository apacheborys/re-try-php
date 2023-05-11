<?php
declare(strict_types=1);

namespace ApacheBorys\Retry\Tests\Functional;

use ApacheBorys\Retry\HandlerExceptionDeclarator\StandardHandlerExceptionDeclarator;
use ApacheBorys\Retry\MessageHandler;
use ApacheBorys\Retry\Tests\Functional\Container\FakeContainer;
use ApacheBorys\Retry\Tests\Functional\Transport\PdoTransportForTests;
use PDO;
use PHPUnit\Framework\TestCase;

class FunctionalTest extends TestCase
{
    private const TRANSPORT_FILE = 'tests/transport.data';

    public function testExecution(): void
    {
        $configFile = __DIR__ . DIRECTORY_SEPARATOR . 'config.json';
        $fileToExec = 'tests/Functional/core-test.php';
        $transportFile = self::TRANSPORT_FILE;

        exec('php ' . $fileToExec);

        $pdo = $this->getPdo($transportFile);

        $this->assertEquals(1, $this->howManyMessagesInDb($pdo));

        $this->assertEquals(1, $this->howManyUnprocessedMessagesInDb($pdo));

        $config = json_decode(file_get_contents($configFile), true);
        $worker = new MessageHandler($config);
        $worker->processRetries(['Some\\Fake\\Class']);

        $this->assertEquals(1, $this->howManyMessagesInDb($pdo));

        $this->assertEquals(1, $this->howManyUnprocessedMessagesInDb($pdo));

        $worker->processRetries();

        $this->assertEquals(1, $this->howManyMessagesInDb($pdo));

        $this->assertEquals(0, $this->howManyUnprocessedMessagesInDb($pdo));

        unlink($transportFile);
    }

    public function testExecutionWithContainer(): void
    {
        $configFile = __DIR__ . DIRECTORY_SEPARATOR . 'config-with-container-usage.json';
        $fileToExec = 'tests/Functional/core-test-with-container-usage.php';
        $transportFile = self::TRANSPORT_FILE;

        exec('php ' . $fileToExec);

        $container = new FakeContainer();

        $pdoTransport = new PdoTransportForTests($transportFile);
        $container->set(PdoTransportForTests::class, $pdoTransport);

        $declarator = new StandardHandlerExceptionDeclarator();
        $container->set(StandardHandlerExceptionDeclarator::class, $declarator);

        $pdo = $this->getPdo($transportFile);

        $this->assertEquals(1, $this->howManyMessagesInDb($pdo));

        $this->assertEquals(1, $this->howManyUnprocessedMessagesInDb($pdo));

        $config = json_decode(file_get_contents($configFile), true);
        $worker = new MessageHandler($config, null, $container);
        $worker->processRetries(['Some\\Fake\\Class']);

        $this->assertEquals(1, $this->howManyMessagesInDb($pdo));

        $this->assertEquals(1, $this->howManyUnprocessedMessagesInDb($pdo));

        $worker->processRetries();

        $this->assertEquals(1, $this->howManyMessagesInDb($pdo));

        $this->assertEquals(0, $this->howManyUnprocessedMessagesInDb($pdo));

        unlink($transportFile);
    }

    private function getPdo(string $transportFile): PDO
    {
        return new PDO('sqlite:' . $transportFile);
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
