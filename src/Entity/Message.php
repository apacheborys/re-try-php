<?php
declare(strict_types=1);

namespace ApacheBorys\Retry\Entity;

use ApacheBorys\Retry\Traits\GetterEntity;

/**
 * @method string getRetryName()
 * @method string getCorrelationId()
 * @method array getPayload()
 * @method int getTryCounter()
 * @method \DateTimeImmutable getShouldBeExecutedAt()
 * @method string getExecutor()
 */
class Message
{
    use GetterEntity;

    private string $retryName;

    private string $correlationId;

    private array $payload;

    private int $tryCounter;

    private \DateTimeImmutable $shouldBeExecutedAt;

    private string $executor;

    public function __construct(
        string $retryName,
        string $correlationId,
        array $payload,
        int $tryCounter,
        \DateTimeImmutable $shouldBeExecutedAt,
        string $executor
    ) {
        $this->retryName = $retryName;
        $this->correlationId = $correlationId;
        $this->payload = $payload;
        $this->tryCounter = $tryCounter;
        $this->shouldBeExecutedAt = $shouldBeExecutedAt;
        $this->executor = $executor;
    }

    public function __toString(): string
    {
        return json_encode([
            'retryName' => $this->retryName,
            'correlationId' => $this->correlationId,
            'payload' => $this->payload,
            'tryCounter' => $this->tryCounter,
            'shouldBeExecutedAt' => $this->shouldBeExecutedAt->format('c'),
            'executor' => $this->executor,
        ]);
    }

    public static function fromArray(array $data): self
    {
        return new Message(
            (string) $data['retryName'],
            (string) $data['correlationId'],
            (array) $data['payload'],
            (int) $data['tryCounter'],
            new \DateTimeImmutable($data['shouldBeExecutedAt']),
            (string) $data['executor']
        );
    }
}
