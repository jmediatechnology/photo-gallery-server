<?php

namespace App\Domain\ValueObject;

use InvalidArgumentException;
use Stringable;

class Title implements Stringable
{
    public const int MAX_LENGTH = 255;

    private string $value;

    public function __construct(string|Stringable $value)
    {
        $isValid = strlen($value) <= self::MAX_LENGTH;
        if ($isValid === false) {
            throw new InvalidArgumentException('Max length exceeded');
        }

        $this->value = $value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
