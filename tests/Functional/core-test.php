<?php

use ApacheBorys\Retry\ExceptionHandler;
use ApacheBorys\Retry\Tests\Functional\Exceptions\Mock;

include 'vendor/autoload.php';

$config = json_decode(file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'config.json'), true);

$retry = new ExceptionHandler($config);
$retry->initHandler();

echo 'I am a test' . PHP_EOL;

throw new Mock('It\'s a test');