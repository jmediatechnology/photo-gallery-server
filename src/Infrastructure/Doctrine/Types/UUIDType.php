<?php

namespace App\Infrastructure\Doctrine\Types;

use App\Domain\ValueObject\UUID;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

class UUIDType extends StringType
{
    public const string NAME = 'uuid';

    public function getName(): string
    {
        return self::NAME;
    }

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getGuidTypeDeclarationSQL($column);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?UUID
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof UUID) {
            return $value;
        }

        return new UUID($value);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value instanceof UUID) {
            return $value->__toString();
        }

        return parent::convertToDatabaseValue($value, $platform);
    }
}
