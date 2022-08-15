<?php
declare(strict_types=1);

namespace ApacheBorys\Retry\Entity;

use ApacheBorys\Retry\Traits\GetterEntity;
use ApacheBorys\Retry\ValueObject\FormulaArgument;
use ApacheBorys\Retry\ValueObject\FormulaOperator;

/**
 * @method FormulaOperator getOperator()
 * @method FormulaArgument getArgument()
 */
class FormulaItem
{
    use GetterEntity;

    private FormulaOperator $operator;

    private FormulaArgument $argument;

    public function __construct(string $operator, string $argument)
    {
        $this->operator = $this->validateOperator($operator);
        $this->argument = $this->validateArgument($argument);
    }

    private function validateOperator(string $operator): FormulaOperator
    {
        return new FormulaOperator($operator);
    }

    private function validateArgument(string $argument): FormulaArgument
    {
        return new FormulaArgument($argument);
    }
}
