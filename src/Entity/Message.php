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
    public const ELEM_ID = 'id';
    public const ELEM_RETRY_NAME = 'retryName';
    public const ELEM_CORRELATION_ID = 'correlationId';
    public const ELEM_PAYLOAD = 'payload';
    public const ELEM_TRY_COUNTER = 'tryCounter';
    public const ELEM_IS_PROCESSED = 'isProcessed';
    public const ELEM_SHOULD_BE_EXECUTED_AT = 'shouldBeExecutedAt';
    public const ELEM_EXECUTOR = 'executor';

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
            self::ELEM_ID => $this->id,
            self::ELEM_RETRY_NAME => $this->retryName,
            self::ELEM_CORRELATION_ID => $this->correlationId,
            self::ELEM_PAYLOAD => $this->payload,
            self::ELEM_TRY_COUNTER => $this->tryCounter,
            self::ELEM_IS_PROCESSED => $this->isProcessed,
            self::ELEM_SHOULD_BE_EXECUTED_AT => $this->shouldBeExecutedAt->format('c'),
            self::ELEM_EXECUTOR => $this->executor,
        ]);
    }

    public static function fromArray(array $data): self
    {
        return new Message(
            (string) $data[self::ELEM_ID],
            (string) $data[self::ELEM_RETRY_NAME],
            (string) $data[self::ELEM_CORRELATION_ID],
            (array) $data[self::ELEM_PAYLOAD],
            (int) $data[self::ELEM_TRY_COUNTER],
            (bool) $data[self::ELEM_IS_PROCESSED],
            new \DateTimeImmutable($data[self::ELEM_SHOULD_BE_EXECUTED_AT]),
            (string) $data[self::ELEM_EXECUTOR]
        );
    }

    public function markAsProcessed(): void
    {
        $this->isProcessed = true;
    }
}
