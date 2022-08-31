<?php
declare(strict_types=1);

namespace ApacheBorys\Retry\ValueObject;

class ArgumentType
{
    public const DIGIT = 'digit';
    public const KEYWORD = 'keyword';

    private string $value;

    public function __construct(string $value)
    {
        if(!in_array($value, $this->getAvailableKeywords(), true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Wrong argument type. Possible values %s',
                    implode(',', $this->getAvailableKeywords()))
            );
        }

        $this->value = $value;
    }

    public static function getAvailableKeywords()
    {
        return [
            self::DIGIT,
            self::KEYWORD,
        ];
    }

    public function __toString()
    {
        return $this->value;
    }
}
