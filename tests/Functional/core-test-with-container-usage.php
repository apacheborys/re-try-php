<?php

use ApacheBorys\Retry\ExceptionHandler;
use ApacheBorys\Retry\HandlerExceptionDeclarator\StandardHandlerExceptionDeclarator;
use ApacheBorys\Retry\Tests\Functional\Container\FakeContainer;
use ApacheBorys\Retry\Tests\Functional\Exceptions\Mock;
use ApacheBorys\Retry\Tests\Functional\Transport\PdoTransportForTests;

include 'vendor/autoload.php';
include 'src/HandlerExceptionDeclarator/StandardHandlerExceptionDeclarator.php';

$config = json_decode(file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'config-with-container-usage.json'), true);

$container = new FakeContainer();

$declarator = new StandardHandlerExceptionDeclarator();
$container->set(StandardHandlerExceptionDeclarator::class, $declarator);

$pdoTransport = new PdoTransportForTests('tests/transport.data');
$container->set(PdoTransportForTests::class, $pdoTransport);

$retry = new ExceptionHandler($config, null, $container);
$retry->initHandler();

echo 'I am a test' . PHP_EOL;

throw new Mock('It\'s a test');