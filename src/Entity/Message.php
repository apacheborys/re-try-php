<?php
declare(strict_types=1);

namespace ApacheBorys\Retry\Entity;

use ApacheBorys\Retry\Traits\GetterEntity;

/**
 * @method string getId()
 * @method string getRetryName()
 * @method string getCorrelationId()
 * @method array getPayload()
 * @method int getTryCounter()
 * @method bool getIsProcessed()
 * @method \DateTimeImmutable getShouldBeExecutedAt()
 * @method string getExecutor()
 */
class Message
{
    use GetterEntity;

    private string $id;
    
    private string $retryName;

    private string $correlationId;

    private array $payload;

    private int $tryCounter;

    private bool $isProcessed;

    private \DateTimeImmutable $shouldBeExecutedAt;

    private string $executor;

    public function __construct(
        string $id,
        string $retryName,
        string $correlationId,
        array $payload,
        int $tryCounter,
        bool $isProcessed,
        \DateTimeImmutable $shouldBeExecutedAt,
        string $executor
    ) {
        $this->id = $id;
        $this->retryName = $retryName;
        $this->correlationId = $correlationId;
        $this->payload = $payload;
        $this->tryCounter = $tryCounter;
        $this->isProcessed = $isProcessed;
        $this->shouldBeExecutedAt = $shouldBeExecutedAt;
        $this->executor = $executor;
    }

    public function __toString(): string
    {
        return json_encode([
            'id' => $this->id,
            'retryName' => $this->retryName,
            'correlationId' => $this->correlationId,
            'payload' => $this->payload,
            'tryCounter' => $this->tryCounter,
            'isProcessed' => $this->isProcessed,
            'shouldBeExecutedAt' => $this->shouldBeExecutedAt->format('c'),
            'executor' => $this->executor,
        ]);
    }

    public static function fromArray(array $data): self
    {
        return new Message(
            (string) $data['id'],
            (string) $data['retryName'],
            (string) $data['correlationId'],
            (array) $data['payload'],
            (int) $data['tryCounter'],
            (bool) $data['isProcessed'],
            new \DateTimeImmutable($data['shouldBeExecutedAt']),
            (string) $data['executor']
        );
    }

    public function markAsProcessed(): void
    {
        $this->isProcessed = true;
    }
}
