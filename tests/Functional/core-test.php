<?php

use ApacheBorys\Retry\ExceptionHandler;
use ApacheBorys\Retry\Tests\Functional\Exceptions\Mock;
use ApacheBorys\Retry\ValueObject\FormulaArgument;

include 'vendor/autoload.php';

$retry = new ExceptionHandler([
    'test' => [
        'exception' => 'ApacheBorys\\Retry\\Tests\\Functional\\Exceptions\\Mock',
        'maxRetries' => 4,
        'formula' => [
            [
                'operator' => '+',
                'argument' => FormulaArgument::QTY_TRIES,
            ],
            [
                'operator' => '*',
                'argument' => '5',
            ]
        ],
        'transport' => [
            'class' => 'ApacheBorys\\Retry\\Tests\\Functional\\Transport\\FileTransportForTests',
            'arguments' => [
                'tests/transport.data'
            ],
        ],
        'executor' => [
            'class' => 'ApacheBorys\\Retry\\Tests\\Functional\\Executor\\Runtime',
            'arguments' => [],
        ]
    ]
]);
$retry->initHandler();

echo 'I am a test' . PHP_EOL;

throw new Mock('It\'s a test');