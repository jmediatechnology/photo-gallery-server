<?php

namespace App\Tests\Unit\Infrastructure\Doctrine\Types;

use App\Domain\ValueObject\UpdatedAt;
use App\Infrastructure\Doctrine\Types\UpdatedAtType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class UpdatedAtTypeTest extends TestCase
{
    #[Test]
    public function canGetName(): void
    {
        $expectedName = UpdatedAtType::NAME;
        $actualName = new UpdatedAtType()->getName();

        $this->assertEquals($expectedName, $actualName);
    }

    #[Test]
    public function throwsInvalidArgumentExceptionInCaseOfConvertToPHPValueNULL(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Failed to convert to php value: UpdatedAt may not be null');

        $abstractPlatform = $this->createStub(AbstractPlatform::class);
        new UpdatedAtType()->convertToPHPValue(null, $abstractPlatform);
    }

    #[Test]
    public function canConvertToDatabaseValueUpdatedAtToUpdatedAt(): void
    {
        $expected = $this->createStub(UpdatedAt::class);

        $abstractPlatform = $this->createStub(AbstractPlatform::class);
        $actual = new UpdatedAtType()->convertToPHPValue($expected, $abstractPlatform);

        $this->assertSame($expected, $actual);
    }

    #[Test]
    public function canConvertToDatabaseValueStringToUpdatedAt(): void
    {
        $expected = '2025-10-02 11:00:00';

        $abstractPlatform = $this->createStub(AbstractPlatform::class);
        $actual = new UpdatedAtType()->convertToPHPValue($expected, $abstractPlatform);

        $this->assertSame($expected, $actual->format('Y-m-d H:i:s'));
    }
}
