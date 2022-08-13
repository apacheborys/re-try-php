<?php
declare(strict_types=1);

namespace ApacheBorys\Retry\Entity;

use ApacheBorys\Retry\Traits\GetterEntity;

/**
 * @method string getRetryName()
 * @method array getPayload()
 * @method int getTryCounter()
 * @method \DateTimeImmutable getShouldBeExecutedAt()
 * @method string getExecutor()
 */
class Message
{
    use GetterEntity;

    private string $retryName;

    private array $payload;

    private int $tryCounter;

    private \DateTimeImmutable $shouldBeExecutedAt;

    private string $executor;

    public function __construct(
        string $retryName,
        array $payload,
        int $tryCounter,
        \DateTimeImmutable $shouldBeExecutedAt,
        string $executor
    ) {
        $this->retryName = $retryName;
        $this->payload = $payload;
        $this->tryCounter = $tryCounter;
        $this->shouldBeExecutedAt = $shouldBeExecutedAt;
        $this->executor = $executor;
    }
}
