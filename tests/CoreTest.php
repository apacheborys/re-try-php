<?php
declare(strict_types=1);

namespace ApacheBorys\Retry\Tests;

use ApacheBorys\Retry\Core;
use ApacheBorys\Retry\ValueObject\FormulaArgument;
use PHPUnit\Framework\TestCase;

class CoreTest extends TestCase
{
    public function testConstruction(): void
    {
        $retry = new Core($this->configurationDataProvider());
        $retry->initHandler();

    }

    public function configurationDataProvider(): array
    {
        return [
            'test' => [
                'exception' => 'ApacheBorys\\Retry\\Tests\\Exceptions\\Mock',
                'maxRetries' => 4,
                'formula' => [
                    [
                        'operator' => '+',
                        'argument' => FormulaArgument::QTY_TRIES,
                    ],
                    [
                        'operator' => '*',
                        'argument' => '5'
                    ]
                ],
                'transport' => [
                    'class' => 'ApacheBorys\\Retry\\Tests\\Transport\\Runtime',
                    'arguments' => [],
                ],
                'executor' => [
                    'class' => 'ApacheBorys\\Retry\\Tests\\Executor\\Runtime',
                    'arguments' => [],
                ]
            ]
        ];
    }
}
