<?php
declare(strict_types=1);

namespace ApacheBorys\Retry\Tests\Functional;

use ApacheBorys\Retry\MessageHandler;
use PHPUnit\Framework\TestCase;

class FunctionalTest extends TestCase
{
    private const TRANSPORT_FILE = 'tests/transport.data';

    public function testExecution(): void
    {
        $output = exec('php tests/Functional/core-test.php');

        $messages = explode(PHP_EOL, file_get_contents(self::TRANSPORT_FILE));
        $this->assertEquals(2, count($messages));

        $config = json_decode(file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'config.json'), true);
        $worker = new MessageHandler($config);
        $worker->processRetries();

        unlink (self::TRANSPORT_FILE);
    }

    public function __destruct()
    {
        if (file_exists(self::TRANSPORT_FILE)) {
            unlink(self::TRANSPORT_FILE);
        }
    }
}
