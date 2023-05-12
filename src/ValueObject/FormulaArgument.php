<?php
declare(strict_types=1);

namespace ApacheBorys\Retry\ValueObject;

use ApacheBorys\Retry\Exceptions\WrongArgument;

class FormulaArgument
{
    public const QTY_TRIES = 'QTY_TRIES';

    private string $value;

    /** @psalm-suppress PropertyNotSetInConstructor */
    private ArgumentType $argumentType;

    public function __construct(string $value)
    {
        $matches = [];
        preg_match_all('/\d/', $value, $matches, PREG_PATTERN_ORDER);

        if (!in_array($value, $this->getAvailableKeywords(), true) && (!isset($matches[0]) || count($matches[0]) === 0)) {
            throw new WrongArgument(
                sprintf(
                    'Argument can contain digits or some specific words only: %s. You provided: %s',
                    implode(", ", $this->getAvailableKeywords()),
                    $value
                )
            );
        }

        if (isset($matches[0]) && count($matches[0]) > 0) {
            $this->argumentType = new ArgumentType(ArgumentType::DIGIT);
        } elseif (in_array($value, $this->getAvailableKeywords(), true)) {
            $this->argumentType = new ArgumentType(ArgumentType::KEYWORD);
        }

        $this->value = $value;
    }

    /**
     * @return string[]
     */
    public static function getAvailableKeywords(): array
    {
        return [
            self::QTY_TRIES,
        ];
    }

    public function getArgumentType(): string
    {
        return (string) $this->argumentType;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
