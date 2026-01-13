<?php

namespace App\Tests\Unit\Infrastructure\Doctrine\Types;

use App\Domain\ValueObject\FilePath;
use App\Infrastructure\Doctrine\Types\FilePathType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class FilePathTypeTest extends TestCase
{
    #[Test]
    public function canGetName(): void
    {
        $expectedName = FilePathType::NAME;
        $actualName = new FilePathType()->getName();

        $this->assertEquals($expectedName, $actualName);
    }

    #[Test]
    public function throwsInvalidArgumentExceptionInCaseOfConvertingToPHPValueByPassingAnNull(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to convert a null value to FilePath.');

        $abstractPlatform = $this->createStub(AbstractPlatform::class);
        new FilePathType()->convertToPHPValue(null, $abstractPlatform);
    }

    #[Test]
    public function canConvertToPHPValueFilePathToFilePath(): void
    {
        $expected = $this->createStub(FilePath::class);

        $abstractPlatform = $this->createStub(AbstractPlatform::class);
        $actual = new FilePathType()->convertToPHPValue($expected, $abstractPlatform);

        $this->assertSame($expected, $actual);
    }

    #[Test]
    public function canConvertToPHPValueStringToFilePath(): void
    {
        $expected = 'Awesome FilePath';

        $abstractPlatform = $this->createStub(AbstractPlatform::class);
        $actual = new FilePathType()->convertToPHPValue($expected, $abstractPlatform);

        $this->assertSame($expected, $actual->__toString());
    }

    #[Test]
    public function canConvertToDatabaseValueObjectToString(): void
    {
        $expected = '/public/images/image.jpg';

        $uuid = $this->createMock(FilePath::class);
        $uuid
            ->expects($this->once())
            ->method('__toString')
            ->willReturn($expected);

        $abstractPlatform = $this->createStub(AbstractPlatform::class);
        $actual = new FilePathType()->convertToDatabaseValue($uuid, $abstractPlatform);

        $this->assertSame($expected, $actual);
    }

    #[Test]
    public function canConvertToDatabaseValueStringToString(): void
    {
        $expected = '/public/images/image.jpg';

        $abstractPlatform = $this->createStub(AbstractPlatform::class);
        $actual = new FilePathType()->convertToDatabaseValue($expected, $abstractPlatform);

        $this->assertSame($expected, $actual);
    }
}
