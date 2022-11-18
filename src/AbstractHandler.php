<?php
declare(strict_types=1);

namespace ApacheBorys\Retry;

use ApacheBorys\Retry\Entity\Config;

abstract class AbstractHandler
{
    /** @var Config[] */
    protected array $config;

    public function __construct(array $config = [])
    {
        $this->config = $this->initConfig($config);
    }

    /**
     * @param array $config
     * @return Config[]
     * @psalm-suppress ArgumentTypeCoercion
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
}
