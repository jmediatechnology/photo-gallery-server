<?php

namespace App\Infrastructure\Doctrine\Types;

use App\Domain\ValueObject\CreatedAt;
use DateMalformedStringException;
use DateTimeImmutable;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use InvalidArgumentException;

class CreatedAtType extends DateTimeImmutableType
{
    public const string NAME = 'created_at';

    public function getName(): string
    {
        return self::NAME;
    }

    /**
     * @throws DateMalformedStringException
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): CreatedAt
    {
        if ($value === null) {
            throw new InvalidArgumentException(
                sprintf('Failed to convert to php value: %s may not be null', 'CreatedAt')
            );
        }

        if ($value instanceof CreatedAt) {
            return $value;
        }

        if (is_string($value)) {
            $value = new DateTimeImmutable($value);
        }

        return new CreatedAt($value);
    }
}
