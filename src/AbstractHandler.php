<?php
declare(strict_types=1);

namespace ApacheBorys\Retry;

use ApacheBorys\Retry\Entity\Config;
use ApacheBorys\Retry\HandlerExceptionDeclarator\StandardHandlerExceptionDeclarator;
use ApacheBorys\Retry\Interfaces\HandlerExceptionDeclaratorInterface;
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
        $this->config = $this->initConfig($config);
        $this->container = $container;
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

        $declaratorClass = $config['handlerExceptionDeclarator']['class'] ?? StandardHandlerExceptionDeclarator::class;
        $this->declarator = new $declaratorClass(...$this->compileArguments($config['handlerExceptionDeclarator']['arguments'] ?? []));

        $this->sendLogRecordInitDeclarator($declaratorClass);

        foreach ($config['items'] as $retryName => $configNode) {
            $result[$retryName] = new Config(
                (string) $retryName,
                (string) $configNode['exception'],
                (int) $configNode['maxRetries'],
                $configNode['formula'],
                new $configNode['transport']['class'](...$this->compileArguments($configNode['transport']['arguments'] ?? [])),
                new $configNode['executor']['class'](...$this->compileArguments($configNode['executor']['arguments'] ?? [])),
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
            if (is_string($arg) && $arg[0] === '@') {
                if ($this->container instanceof ContainerInterface && $this->container->has(substr($arg, 1))) {
                    $result[] = $this->container->get(substr($arg, 1));
                    continue;
                } else {
                    throw new \LogicException(sprintf('Can\'t get %s instance from container to instantiate Transport or Executor', substr($arg, 1)));
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
}
