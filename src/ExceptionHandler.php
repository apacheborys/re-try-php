<?php
declare(strict_types=1);

namespace ApacheBorys\Retry;

use ApacheBorys\Retry\Entity\{Config, FormulaItem, Message};
use ApacheBorys\Retry\Exceptions\WrongArgument;
use ApacheBorys\Retry\ValueObject\{ArgumentType, FormulaArgument};

class ExceptionHandler extends AbstractHandler
{
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
                    $tryNumber = $this->getTryNumber($exception, $retryConfig);

                    if ($tryNumber < $retryConfig->getMaxRetries()) {
                        $retryConfig->getTransport()->send(
                            new Message(
                                $retryConfig->getTransport()->getNextId($exception, $retryConfig),
                                $retryConfig->getName(),
                                $retryConfig->getExecutor()->getCorrelationId($exception, $retryConfig),
                                $this->compilePayload($exception, $retryConfig),
                                $tryNumber,
                                false,
                                $this->calculateNextTimeForTry($exception, $retryConfig),
                                get_class($retryConfig->getExecutor())
                            )
                        );
                    }

                    throw $exception;
                }
            }
        };
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
