<?php

namespace App\Infrastructure\Doctrine\Types;

use App\Domain\ValueObject\Title;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;
use InvalidArgumentException;

class TitleType extends StringType
{
    private const int LENGTH = 255;

    public const string NAME = 'title';

    public function getName(): string
    {
        return self::NAME;
    }

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getStringTypeDeclarationSQL([...$column, 'length' =>  self::LENGTH]);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): Title
    {
        if ($value === null) {
            throw new InvalidArgumentException('Unable to convert a null value to Title.');
        }

        if ($value instanceof Title) {
            return $value;
        }

        return new Title($value);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value instanceof Title) {
            return $value->__toString();
        }

        return parent::convertToDatabaseValue($value, $platform);
    }
}
