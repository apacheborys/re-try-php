<?php
declare(strict_types=1);

namespace ApacheBorys\Retry;

use ApacheBorys\Retry\Entity\Config;
use ApacheBorys\Retry\HandlerExceptionDefiner\StandardHandlerExceptionDeclarator;
use ApacheBorys\Retry\Interfaces\HandlerExceptionDeclaratorInterface;

abstract class AbstractHandler
{
    /** @var Config[] */
    protected array $config;

    protected HandlerExceptionDeclaratorInterface $declarator;

    public function __construct(array $config = [])
    {
        $this->config = $this->initConfig($config);
    }

    /**
     * @param array $config
     * @return Config[]
     * @psalm-suppress ArgumentTypeCoercion
     * @psalm-suppress PropertyTypeCoercion
     */
    private function initConfig(array $config): array
    {
        $result = [];

        $definerClass = $config['handlerExceptionDeclarator']['class'] ?? StandardHandlerExceptionDeclarator::class;
        $this->declarator = new $definerClass(...$config['handlerExceptionDeclarator']['arguments'] ?? []);

        foreach ($config['items'] as $retryName => $configNode) {
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
