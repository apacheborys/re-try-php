<?php
declare(strict_types=1);

namespace ApacheBorys\Retry\Tests\Functional;

use ApacheBorys\Retry\MessageHandler;
use ApacheBorys\Retry\Tests\Functional\Exceptions\Mock;
use PHPUnit\Framework\TestCase;

class FunctionalTest extends TestCase
{
    private const TRANSPORT_FILE = 'tests/transport.data';
    private const CONFIG_FILE = __DIR__ . DIRECTORY_SEPARATOR . 'config.json';

    public function testExecution(): void
    {
        exec('php tests/Functional/core-test.php');

        $messages = explode(PHP_EOL, file_get_contents(self::TRANSPORT_FILE));
        $this->assertEquals(2, count($messages));

        $this->assertTrue(is_int(strpos(file_get_contents(self::TRANSPORT_FILE), '"isProcessed":false')));

        $config = json_decode(file_get_contents(self::CONFIG_FILE), true);
        $worker = new MessageHandler($config);
        $worker->processRetries([Mock::class]);

        $messages = explode(PHP_EOL, file_get_contents(self::TRANSPORT_FILE));
        $this->assertEquals(2, count($messages));

        $this->assertTrue(is_int(strpos(file_get_contents(self::TRANSPORT_FILE), '"isProcessed":false')));

        $worker->processRetries();

        $messages = explode(PHP_EOL, file_get_contents(self::TRANSPORT_FILE));
        $this->assertEquals(2, count($messages));

        $this->assertTrue(is_int(strpos(file_get_contents(self::TRANSPORT_FILE), '"isProcessed":true')));

        unlink(self::TRANSPORT_FILE);
    }

    public function __destruct()
    {
        if (file_exists(self::TRANSPORT_FILE)) {
            unlink(self::TRANSPORT_FILE);
        }
    }
}
