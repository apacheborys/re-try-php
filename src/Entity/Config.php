<?php
declare(strict_types=1);

namespace ApacheBorys\Retry\Entity;

use ApacheBorys\Retry\Interfaces\Executor;
use ApacheBorys\Retry\Interfaces\Transport;
use ApacheBorys\Retry\Traits\GetterEntity;

/**
 * @method string getName()
 * @method string getHandledException()
 * @method int getMaxRetries()
 * @method FormulaItem[] getFormulaToCalculateTimeForNextTry()
 * @method Transport getTransport()
 * @method Executor getExecutor()
 */
class Config
{
    use GetterEntity;

    private string $name;

    private string $handledException;

    private int $maxRetries;

    private array $formulaToCalculateTimeForNextTry;

    private Transport $transport;

    private Executor $executor;

    public function __construct(
        string $name,
        string $handledException,
        int $maxRetries,
        array $formulaToCalculateTimeForNextTry,
        Transport $transport,
        Executor $executor
    ) {
        $this->name = $name;
        $this->handledException = $handledException;
        $this->maxRetries = $maxRetries;
        $this->formulaToCalculateTimeForNextTry = $formulaToCalculateTimeForNextTry;
        $this->transport = $transport;
        $this->executor = $executor;
    }
}
