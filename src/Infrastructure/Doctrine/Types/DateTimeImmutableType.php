<?php

namespace App\Infrastructure\Doctrine\Types;

use App\Domain\ValueObject\CreatedAt;
use App\Domain\ValueObject\UpdatedAt;
use DateTimeInterface;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class DateTimeImmutableType extends Type
{
    public const string NAME = 'custom_datetime_immutable';

    public function getName(): string
    {
        return self::NAME;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value instanceof UpdatedAt) {
            return $value->format('Y-m-d H:i:s');
        }
        if ($value instanceof CreatedAt) {
            return $value->format('Y-m-d H:i:s');
        }

        if ($value instanceof DateTimeInterface) {
            return $value->format($platform->getDateTimeFormatString());
        }

        if (date_create_immutable($value) instanceof DateTimeInterface) {
            return $value;
        }

        return null;
    }

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getDateTimeTypeDeclarationSQL($column);
    }
}
