<?php
declare(strict_types=1);

namespace ApacheBorys\Retry;

use ApacheBorys\Retry\Entity\Config;
use ApacheBorys\Retry\HandlerExceptionDeclarator\StandardHandlerExceptionDeclarator;
use ApacheBorys\Retry\Interfaces\HandlerExceptionDeclaratorInterface;
use ApacheBorys\Retry\Traits\LogWrapper;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

abstract class AbstractHandler
{
    public const LOG_PREFIX = 'Retry lib: ';

    use LogWrapper;

    /** @var Config[] */
    protected array $config;

    protected ?LoggerInterface $logger;

    protected HandlerExceptionDeclaratorInterface $declarator;

    public function __construct(array $config = [], LoggerInterface $logger = null)
    {
        $this->logger = $logger;
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

        $declaratorClass = $config['handlerExceptionDeclarator']['class'] ?? StandardHandlerExceptionDeclarator::class;
        $this->declarator = new $declaratorClass(...$config['handlerExceptionDeclarator']['arguments'] ?? []);

        $this->sendLogRecordInitDeclarator($declaratorClass);

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
}
