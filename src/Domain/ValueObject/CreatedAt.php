<?php

namespace App\Domain\ValueObject;

use DateMalformedStringException;
use DateTimeImmutable;
use Stringable;

class CreatedAt implements Stringable
{
    public function __construct(private DateTimeImmutable $value) {}

    public function __toString(): string
    {
        return $this->value->format('d-m-Y H:i:s');
    }

    public function format(string $format): string
    {
        return $this->value->format($format);
    }

    /**
     * @throws DateMalformedStringException
     */
    public static function fromString(string|Stringable $string): self
    {
        return new self(new DateTimeImmutable($string));
    }
}
