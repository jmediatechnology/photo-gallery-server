<?php

namespace App\Tests\Unit\Infrastructure\Doctrine\Types;

use App\Domain\ValueObject\CreatedAt;
use App\Infrastructure\Doctrine\Types\CreatedAtType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CreatedAtTypeTest extends TestCase
{
    #[Test]
    public function canGetName(): void
    {
        $expectedName = CreatedAtType::NAME;
        $actualName = new CreatedAtType()->getName();

        $this->assertEquals($expectedName, $actualName);
    }

    #[Test]
    public function throwsInvalidArgumentExceptionInCaseOfConvertToPHPValueNULL(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Failed to convert to php value: CreatedAt may not be null');

        $abstractPlatform = $this->createStub(AbstractPlatform::class);
        new CreatedAtType()->convertToPHPValue(null, $abstractPlatform);
    }

    #[Test]
    public function canConvertToDatabaseValueCreatedAtToCreatedAt(): void
    {
        $expected = $this->createStub(CreatedAt::class);

        $abstractPlatform = $this->createStub(AbstractPlatform::class);
        $actual = new CreatedAtType()->convertToPHPValue($expected, $abstractPlatform);

        $this->assertSame($expected, $actual);
    }

    #[Test]
    public function canConvertToDatabaseValueStringToCreatedAt(): void
    {
        $expected = '2025-10-02 11:00:00';

        $abstractPlatform = $this->createStub(AbstractPlatform::class);
        $actual = new CreatedAtType()->convertToPHPValue($expected, $abstractPlatform);

        $this->assertSame($expected, $actual->format('Y-m-d H:i:s'));
    }
}
