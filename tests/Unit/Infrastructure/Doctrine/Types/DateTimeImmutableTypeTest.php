<?php

namespace App\Tests\Unit\Infrastructure\Doctrine\Types;

use App\Domain\ValueObject\CreatedAt;
use App\Domain\ValueObject\UpdatedAt;
use App\Infrastructure\Doctrine\Types\DateTimeImmutableType;
use DateTimeImmutable;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class DateTimeImmutableTypeTest extends TestCase
{
    #[Test]
    public function canGetName(): void
    {
        $expectedName = DateTimeImmutableType::NAME;
        $actualName = new DateTimeImmutableType()->getName();

        $this->assertEquals($expectedName, $actualName);
    }

    #[Test]
    public function canConvertToDatabaseValueThatIsUpdatedAt(): void
    {
        $expected = '2025-10-02 11:00:00';

        $updatedAt = $this->createMock(UpdatedAt::class);
        $updatedAt
            ->expects($this->once())
            ->method('format')
            ->with('Y-m-d H:i:s')
            ->willReturn($expected);

        $abstractPlatform = $this->createStub(AbstractPlatform::class);

        $actual = new DateTimeImmutableType()->convertToDatabaseValue($updatedAt, $abstractPlatform);

        $this->assertSame($expected, $actual);
    }

    #[Test]
    public function canConvertToDatabaseValueThatIsCreatedAt(): void
    {
        $expected = '2025-10-02 11:00:00';

        $createdAt = $this->createMock(CreatedAt::class);
        $createdAt
            ->expects($this->once())
            ->method('format')
            ->with('Y-m-d H:i:s')
            ->willReturn($expected);

        $abstractPlatform = $this->createStub(AbstractPlatform::class);

        $actual = new DateTimeImmutableType()->convertToDatabaseValue($createdAt, $abstractPlatform);

        $this->assertSame($expected, $actual);
    }

    #[Test]
    public function canConvertToDatabaseValueThatIsDateTimeImmutable(): void
    {
        $expected = '2025-10-02 11:00:00';

        $abstractPlatform = $this->createMock(AbstractPlatform::class);
        $abstractPlatform
            ->expects($this->once())
            ->method('getDateTimeFormatString')
            ->willReturn('Y-m-d H:i:s');

        $actual = new DateTimeImmutableType()->convertToDatabaseValue(new DateTimeImmutable($expected), $abstractPlatform);

        $this->assertSame($expected, $actual);
    }

    #[Test]
    public function canConvertToDatabaseValueThatIsDateTimeString(): void
    {
        $expected = '2025-10-02 12:00:00';

        $abstractPlatform = $this->createStub(AbstractPlatform::class);

        $actual = new DateTimeImmutableType()->convertToDatabaseValue($expected, $abstractPlatform);

        $this->assertSame($expected, $actual);
    }

    #[Test]
    public function canConvertToDatabaseValueThatIsInvalidToNull(): void
    {
        $value = 'non-sensical database value';

        $abstractPlatform = $this->createStub(AbstractPlatform::class);

        $actual = new DateTimeImmutableType()->convertToDatabaseValue($value, $abstractPlatform);

        $this->assertNull($actual);
    }
}
