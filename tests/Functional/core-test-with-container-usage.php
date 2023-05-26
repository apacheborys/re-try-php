<?php

use ApacheBorys\Retry\HandlerExceptionDeclarator\StandardHandlerExceptionDeclarator;
use ApacheBorys\Retry\HandlerFactory;
use ApacheBorys\Retry\Tests\Functional\Container\FakeContainer;
use ApacheBorys\Retry\Tests\Functional\Exceptions\Mock;
use ApacheBorys\Retry\Tests\Functional\Logger\FakeLogger;

include 'vendor/autoload.php';
include 'src/HandlerExceptionDeclarator/StandardHandlerExceptionDeclarator.php';

$config = json_decode(file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'config-with-container-usage.json'), true);

$container = new FakeContainer();

$declarator = new StandardHandlerExceptionDeclarator();
$container->set(StandardHandlerExceptionDeclarator::class, $declarator);

$logger = new FakeLogger();
$container->set(FakeLogger::class, $logger);

$factory = new HandlerFactory($config);
$retry = $factory->createExceptionHandler($container);
$retry->initHandler();

echo 'I am a test' . PHP_EOL;

throw new Mock('It\'s a test');