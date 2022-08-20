<?php
declare(strict_types=1);

namespace ApacheBorys\Retry\Tests\Functional;

use ApacheBorys\Retry\Tests\Functional\Exceptions\Mock;
use ApacheBorys\Retry\Tests\Mock\TestCore;
use ApacheBorys\Retry\ValueObject\FormulaArgument;
use PHPUnit\Framework\TestCase;

class CoreTest extends TestCase
{
    public function testConstruction(): void
    {
        $retry = new TestCore($this->configurationDataProvider());
        $retry->initHandler();

        $this->expectException(Mock::class);
        $this->throwException(new Mock('Test exception'));

        $messages = $retry->getConfig('test')->getTransport()->getMessages();
        $this->assertEquals(1, count($messages));
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
