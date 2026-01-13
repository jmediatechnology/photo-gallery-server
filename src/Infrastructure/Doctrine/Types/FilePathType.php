<?php

namespace App\Infrastructure\Doctrine\Types;

use App\Domain\ValueObject\FilePath;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;
use InvalidArgumentException;

class FilePathType extends StringType
{
    private const int LENGTH = 255;

    public const string NAME = 'file_path';

    public function getName(): string
    {
        return self::NAME;
    }

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getStringTypeDeclarationSQL([...$column, 'length' =>  self::LENGTH]);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): FilePath
    {
        if ($value === null) {
            throw new InvalidArgumentException('Unable to convert a null value to FilePath.');
        }

        if ($value instanceof FilePath) {
            return $value;
        }

        return new FilePath($value);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value instanceof FilePath) {
            return $value->__toString();
        }

        return parent::convertToDatabaseValue($value, $platform);
    }
}
