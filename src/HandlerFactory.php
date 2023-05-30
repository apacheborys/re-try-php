<?php

declare(strict_types=1);

namespace ApacheBorys\Retry;

use ApacheBorys\Retry\Entity\Config;
use ApacheBorys\Retry\HandlerExceptionDeclarator\StandardHandlerExceptionDeclarator;
use ApacheBorys\Retry\Interfaces\Executor;
use ApacheBorys\Retry\Interfaces\HandlerExceptionDeclaratorInterface;
use ApacheBorys\Retry\Interfaces\Transport;
use ApacheBorys\Retry\Traits\LogWrapper;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class HandlerFactory
{
    use LogWrapper;

    private array $config;

    private ?LoggerInterface $logger;

    public function __construct(array $config, LoggerInterface $logger = null)
    {
        $this->config = $config;
        $this->logger = $logger;
    }

    public function createExceptionHandler(?ContainerInterface $container = null): ExceptionHandler
    {
        $handler = new ExceptionHandler($this->compileConfig($container), $this->instantiateDeclarator($container), $this->logger);
        $handler->initHandler();

        return $handler;
    }

    public function createMessageHandler(?ContainerInterface $container = null): MessageHandler
    {
        return new MessageHandler($this->compileConfig($container), $this->instantiateDeclarator($container), $this->logger);
    }

    /**
     * @return Config[]
     * @psalm-suppress ArgumentTypeCoercion
     * @psalm-suppress PropertyTypeCoercion
     */
    private function compileConfig(?ContainerInterface $container): array
    {
        $result = [];

        foreach ($this->config['items'] as $retryName => $configNode) {
            $result[$retryName] = new Config(
                (string) $retryName,
                (string) $configNode['exception'],
                (int) $configNode['maxRetries'],
                $configNode['formula'],
                $this->instantiateClass(
                    $configNode['transport']['class'],
                    $configNode['transport']['arguments'] ?? [],
                    Transport::class,
                    $container
                ),
                $this->instantiateClass(
                    $configNode['executor']['class'],
                    $configNode['executor']['arguments'] ?? [],
                    Executor::class,
                    $container
                )
            );
        }

        $this->sendLogRecordInitConfigs(count($result));

        return $result;
    }

    private function instantiateDeclarator(?ContainerInterface $container): HandlerExceptionDeclaratorInterface
    {
        /** @var HandlerExceptionDeclaratorInterface $declarator */
        $declarator = $this->instantiateClass(
            $this->config['handlerExceptionDeclarator']['class'] ?? StandardHandlerExceptionDeclarator::class,
            $this->config['handlerExceptionDeclarator']['arguments'] ?? [],
            HandlerExceptionDeclaratorInterface::class,
            $container
        );

        $this->sendLogRecordInitDeclarator(get_class($declarator));

        return $declarator;
    }

    private function sendLogRecordInitDeclarator(string $declaratorClass): void
    {
        $this->sendLogRecord(
            LogLevel::INFO,
            sprintf('Init for %s with handler exception declarator %s', get_class($this), $declaratorClass)
        );
    }

    private function sendLogRecordInitConfigs(int $qty): void
    {
        $this->sendLogRecord(LogLevel::INFO, sprintf('Init for %s configs. Quantity: %d', get_class($this), $qty));
    }

    private function compileArguments(array $arguments, ?ContainerInterface $container): array
    {
        $result = [];

        foreach ($arguments as $arg) {
            if (is_string($arg)) {
                $tempResult = $this->checkClassInContainer($arg, $container);

                if (!is_null($tempResult)) {
                    $result[] = $tempResult;
                    continue;
                }
            }

            if (is_array($arg) && isset($arg['class'], $arg['arguments'])) {
                $result[] = new $arg['class'](...$this->compileArguments($arg['arguments'], $container));
                continue;
            }

            $result[] = $arg;
        }

        return $result;
    }

    /**
     * @return HandlerExceptionDeclaratorInterface|Transport|Executor
     */
    private function instantiateClass(
        string $class,
        array $arguments,
        string $shouldBeInstanceOf,
        ?ContainerInterface $container
    ): object {
        $result = $this->checkClassInContainer($class, $container) ?? new $class(...$this->compileArguments($arguments, $container));

        if (!($result instanceof $shouldBeInstanceOf)) {
            throw new \LogicException(
                sprintf('The generated class %s is not an inctance of %', get_class($result), $shouldBeInstanceOf)
            );
        }

        return $result;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function checkClassInContainer(string $class, ?ContainerInterface $container): ?object
    {
        if ($class[0] === '@') {
            if ($container instanceof ContainerInterface && $container->has(substr($class, 1))) {
                return $container->get(substr($class, 1));
            } else {
                throw new \LogicException(
                    sprintf(
                        'Can\'t get %s instance from container to instantiate Transport or Executor',
                        substr($class, 1)
                    )
                );
            }
        }

        return null;
    }
}
