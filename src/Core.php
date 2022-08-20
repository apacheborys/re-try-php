<?php
declare(strict_types=1);

namespace ApacheBorys\Retry;

use ApacheBorys\Retry\Entity\Config;
use ApacheBorys\Retry\Entity\FormulaItem;
use ApacheBorys\Retry\Entity\Message;
use ApacheBorys\Retry\Exceptions\WrongArgument;
use ApacheBorys\Retry\ValueObject\ArgumentType;
use ApacheBorys\Retry\ValueObject\FormulaArgument;

class Core
{
    /** @var Config[] */
    protected array $config;

    public function __construct(array $config = [])
    {
        $this->config = $this->initConfig($config);
    }

    /**
     * Should be called externally by application
     */
    public function initHandler(): void
    {
        set_exception_handler(
            $this->getHandling($this->config)
        );
    }

    /**
     * @param Config[] $config
     * @return callable
     */
    private function getHandling($config): callable
    {
        return function (\Throwable $exception) use ($config) {
            foreach ($config as $retryConfig) {
                if (get_class($exception) === $retryConfig->getHandledException()) {
                    $retryConfig->getTransport()->send(
                        new Message(
                            $retryConfig->getName(),
                            $this->compilePayload($exception, $retryConfig),
                            $this->getTryNumber($exception, $retryConfig),
                            $this->calculateNextTimeForTry($exception, $retryConfig),
                            get_class($retryConfig->getExecutor())
                        )
                    );

                    throw $exception;
                }
            }
        };
    }

    /**
     * @param array $config
     * @return Config[]
     */
    private function initConfig(array $config): array
    {
        $result = [];

        foreach ($config as $retryName => $configNode) {
            $result[$retryName] = new Config(
                (string) $retryName,
                (string) $configNode['exception'],
                (int) $configNode['maxRetries'],
                $configNode['formula'],
                new $configNode['transport']['class'](...$configNode['transport']['arguments'] ?? []),
                new $configNode['executor']['class'](...$configNode['executor']['arguments'] ?? []),
            );
        }

        return $result;
    }

    private function compilePayload(\Throwable $exception, Config $config): array
    {
        return $config->getExecutor()->compilePayload($exception, $config);
    }

    private function getTryNumber(\Throwable $exception, Config $config): int
    {
        $tryQty = $config->getTransport()->howManyTriesWasBefore($exception, $config);

        return ($tryQty + 1);
    }

    private function calculateNextTimeForTry(\Throwable $exception, Config $config): \DateTimeImmutable
    {
        $shift = 0;

        foreach ($config->getFormulaToCalculateTimeForNextTry() as $formulaItem) {
            eval('$shift = $shift ' . $formulaItem->getOperator() . ' ' . $this->compileArgument($formulaItem, $exception, $config) . ';');
        }

        $currentTime = new \DateTimeImmutable();

        return $currentTime->modify('+' . $shift . ' second');
    }

    private function compileArgument(FormulaItem $item, \Throwable $exception, Config $config): string
    {
        switch ($item->getArgument()->getArgumentType()) {
            case ArgumentType::DIGIT:
                return (string) $item->getArgument();
            case ArgumentType::KEYWORD:
                if ($item->getArgument()->__toString() === FormulaArgument::QTY_TRIES) {
                    return (string) $config->getTransport()->howManyTriesWasBefore($exception, $config);
                } else {
                    throw new WrongArgument(
                        sprintf(
                            'Undefined keyword. Possible values %s',
                            implode(',', FormulaArgument::getAvailableKeywords())
                        )
                    );
                }
            default:
                throw new \InvalidArgumentException(
                    sprintf(
                        'Unexpected argument type, it can be %s',
                        implode(', ', ArgumentType::getAvailableKeywords())
                    )
                );
        }
    }
}
