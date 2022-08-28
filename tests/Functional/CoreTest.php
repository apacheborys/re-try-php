<?php
declare(strict_types=1);

namespace ApacheBorys\Retry\Tests\Functional;

use PHPUnit\Framework\TestCase;

class CoreTest extends TestCase
{
    private const TRANSPORT_FILE = 'tests/transport.data';

    public function testExecution(): void
    {
        $output = exec('php tests/Functional/core-test.php');

        $this->assertTrue((bool) strpos($output,'tests/Functional/core-test.php on line 39'));
        $messages = explode(PHP_EOL, file_get_contents(self::TRANSPORT_FILE));
        $this->assertEquals(2, count($messages));

        unlink (self::TRANSPORT_FILE);
    }

    public function __destruct()
    {
        if (file_exists(self::TRANSPORT_FILE)) {
            unlink(self::TRANSPORT_FILE);
        }
    }
}
