<?php

namespace App\Infrastructure\Doctrine\Types;

use App\Domain\ValueObject\Description;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

class DescriptionType extends StringType
{
    private const int LENGTH = 512;

    public const string NAME = 'description';

    public function getName(): string
    {
        return self::NAME;
    }

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getStringTypeDeclarationSQL([...$column, 'length' =>  self::LENGTH]);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): Description
    {
        if ($value instanceof Description) {
            return $value;
        }

        if ($value === null) {
            $value = '';
        }

        return new Description($value);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value instanceof Description) {
            return $value->__toString();
        }

        return parent::convertToDatabaseValue($value, $platform);
    }
}
