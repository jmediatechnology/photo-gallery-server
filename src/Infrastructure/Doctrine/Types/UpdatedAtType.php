<?php

namespace App\Infrastructure\Doctrine\Types;

use App\Domain\ValueObject\UpdatedAt;
use DateMalformedStringException;
use DateTimeImmutable;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use InvalidArgumentException;

class UpdatedAtType extends DateTimeImmutableType
{
    public const string NAME = 'updated_at';

    public function getName(): string
    {
        return self::NAME;
    }

    /**
     * @throws DateMalformedStringException
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): UpdatedAt
    {
        if ($value === null) {
            throw new InvalidArgumentException(
                sprintf('Failed to convert to php value: %s may not be null', 'UpdatedAt')
            );
        }

        if ($value instanceof UpdatedAt) {
            return $value;
        }

        if (is_string($value)) {
            $value = new DateTimeImmutable($value);
        }

        return new UpdatedAt($value);
    }
}
