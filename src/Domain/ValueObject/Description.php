<?php

namespace App\Domain\ValueObject;

use InvalidArgumentException;
use Stringable;

class Description implements Stringable
{
    public function __construct(private string|Stringable $value)
    {
         $this->value = (string)$value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
