<?php
declare(strict_types=1);

namespace ApacheBorys\Retry;

use ApacheBorys\Retry\Entity\Config;
use ApacheBorys\Retry\HandlerExceptionDeclarator\StandardHandlerExceptionDeclarator;
use ApacheBorys\Retry\Interfaces\Executor;
use ApacheBorys\Retry\Interfaces\HandlerExceptionDeclaratorInterface;
use ApacheBorys\Retry\Interfaces\Transport;
use ApacheBorys\Retry\Traits\LogWrapper;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

abstract class AbstractHandler
{
    public const LOG_PREFIX = 'Retry lib: ';

    use LogWrapper;

    /** @var Config[] */
    protected array $config;

    protected ?LoggerInterface $logger;

    private ?ContainerInterface $container;

    protected HandlerExceptionDeclaratorInterface $declarator;

    public function __construct(array $config = [], LoggerInterface $logger = null, ContainerInterface $container = null)
    {
        $this->logger = $logger;
        $this->container = $container;
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

        $this->declarator = $this->instantiateClass(
            $config['handlerExceptionDeclarator']['class'] ?? StandardHandlerExceptionDeclarator::class,
            $config['handlerExceptionDeclarator']['arguments'] ?? [],
            HandlerExceptionDeclaratorInterface::class
        );

        $this->sendLogRecordInitDeclarator(get_class($this->declarator));

        foreach ($config['items'] as $retryName => $configNode) {
            $result[$retryName] = new Config(
                (string) $retryName,
                (string) $configNode['exception'],
                (int) $configNode['maxRetries'],
                $configNode['formula'],
                $this->instantiateClass(
                    $configNode['transport']['class'],
                    $configNode['transport']['arguments'] ?? [],
                    Transport::class
                ),
                $this->instantiateClass(
                    $configNode['executor']['class'],
                    $configNode['executor']['arguments'] ?? [],
                    Executor::class
                )
            );
        }

        $this->sendLogRecordInitConfigs(count($result));

        return $result;
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

    private function compileArguments(array $arguments): array
    {
        $result = [];

        foreach ($arguments as $arg) {
            if (is_string($arg)) {
                $tempResult = $this->checkClassInContainer($arg);

                if (!is_null($tempResult)) {
                    $result[] = $tempResult;
                    continue;
                }
            }

            if (is_array($arg) && isset($arg['class'], $arg['arguments'])) {
                $result[] = new $arg['class'](...$this->compileArguments($arg['arguments']));
                continue;
            }

            $result[] = $arg;
        }

        return $result;
    }

    /**
     * @return HandlerExceptionDeclaratorInterface|Transport|Executor
     */
    private function instantiateClass(string $class, array $arguments, string $shouldBeInstanceOf): object
    {
        $result = $this->checkClassInContainer($class) ?? new $class(...$this->compileArguments($arguments));

        if (!($result instanceof $shouldBeInstanceOf)) {
            throw new \LogicException(
                sprintf('The generated class %s is not an inctance of %', get_class($result), $shouldBeInstanceOf)
            );
        }

        return $result;
    }

    private function checkClassInContainer(string $class): ?object
    {
        if ($class[0] === '@') {
            if ($this->container instanceof ContainerInterface && $this->container->has(substr($class, 1))) {
                return $this->container->get(substr($class, 1));
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
