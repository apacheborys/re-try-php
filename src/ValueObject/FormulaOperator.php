<?php
declare(strict_types=1);

namespace ApacheBorys\Retry\ValueObject;

use ApacheBorys\Retry\Exceptions\WrongOperator;

class FormulaOperator
{
    private string $value;

    public function __construct(string $value)
    {
        if (strlen($value) > 1) {
            throw new WrongOperator('Length is too long. It can be only one char');
        }

        $matches = [];
        preg_match('/\*|\/|\+|\-/', $value, $matches);
        if (count($matches) === 0) {
            throw new WrongOperator('Can\'t find any valuable operator. It can be *, /, + and -');
        }

        $this->value = $value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
