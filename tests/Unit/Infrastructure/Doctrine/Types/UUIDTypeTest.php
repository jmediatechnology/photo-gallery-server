<?php

namespace App\Tests\Unit\Infrastructure\Doctrine\Types;

use App\Domain\ValueObject\UUID;
use App\Infrastructure\Doctrine\Types\UUIDType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class UUIDTypeTest extends TestCase
{
    #[Test]
    public function canGetSQLDeclaration(): void
    {
        $column = [];

        $abstractPlatform = $this->createMock(AbstractPlatform::class);
        $abstractPlatform
            ->expects($this->once())
            ->method('getGuidTypeDeclarationSQL')
            ->with($column)
            ->willReturn('VARCHAR(36)');

        new UUIDType()->getSQLDeclaration($column, $abstractPlatform);
    }

    #[Test]
    public function canGetName(): void
    {
        $expectedName = UUIDType::NAME;
        $actualName = new UUIDType()->getName();

        $this->assertEquals($expectedName, $actualName);
    }

    #[Test]
    public function canConvertToPHPValueNullToNull(): void
    {
        $abstractPlatform = $this->createStub(AbstractPlatform::class);
        $actual = new UUIDType()->convertToPHPValue(null, $abstractPlatform);

        $this->assertNull($actual);
    }

    #[Test]
    public function canConvertToPHPValueUUIDToUUID(): void
    {
        $expected = $this->createStub(UUID::class);

        $abstractPlatform = $this->createStub(AbstractPlatform::class);
        $actual = new UUIDType()->convertToPHPValue($expected, $abstractPlatform);

        $this->assertSame($expected, $actual);
    }

    #[Test]
    public function canConvertToPHPValueStringToUUID(): void
    {
        $expected = 'd9e7a184-5d5b-11ea-a62a-3499710062d0';

        $abstractPlatform = $this->createStub(AbstractPlatform::class);
        $actual = new UUIDType()->convertToPHPValue($expected, $abstractPlatform);

        $this->assertInstanceOf(UUID::class, $actual);
        $this->assertSame($expected, $actual->__toString());
    }

    #[Test]
    public function canConvertToDatabaseValueUUIDToString(): void
    {
        $expected = 'd9e7a184-5d5b-11ea-a62a-3499710062d0';

        $uuid = $this->createMock(UUID::class);
        $uuid
            ->expects($this->once())
            ->method('__toString')
            ->willReturn($expected);

        $abstractPlatform = $this->createStub(AbstractPlatform::class);
        $actual = new UUIDType()->convertToDatabaseValue($uuid, $abstractPlatform);

        $this->assertSame($expected, $actual);
    }

    #[Test]
    public function canConvertToDatabaseValueStringToString(): void
    {
        $expected = 'd9e7a184-5d5b-11ea-a62a-3499710062d0';

        $abstractPlatform = $this->createStub(AbstractPlatform::class);
        $actual = new UUIDType()->convertToDatabaseValue($expected, $abstractPlatform);

        $this->assertSame($expected, $actual);
    }
}
