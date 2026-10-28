<?php

namespace App\Domain\ValueObject;

use InvalidArgumentException;
use Stringable;

class UUID implements Stringable
{
    private string $value;

    public function __construct(string|Stringable $value)
    {
        $isValid = (bool)preg_match("/^[{]?[0-9a-fA-F]{8}-([0-9a-fA-F]{4}-){3}[0-9a-fA-F]{12}[}]?$/", $value);
        if ($isValid === false) {
            throw new InvalidArgumentException(sprintf('Invalid uuid provided: "%s"', $value));
        }

        $this->value = (string)$value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
