<?php
declare(strict_types=1);

namespace ApacheBorys\Retry;

use ApacheBorys\Retry\Entity\Config;
use ApacheBorys\Retry\Entity\Message;

class Core
{
    /** @var Config[] */
    private array $config;

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
        // @TODO should be implemented before release
        return [];
    }

    private function getTryNumber(\Throwable $exception, Config $config): int
    {
        // @TODO should be implemented before release
        return 1;
    }

    private function calculateNextTimeForTry(\Throwable $exception, Config $config): \DateTimeImmutable
    {
        // @TODO should be implemented before release
        return new \DateTimeImmutable();
    }
}
