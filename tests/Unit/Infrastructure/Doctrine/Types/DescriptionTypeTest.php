<?php

namespace App\Tests\Unit\Infrastructure\Doctrine\Types;

use App\Domain\ValueObject\Description;
use App\Infrastructure\Doctrine\Types\DescriptionType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class DescriptionTypeTest extends TestCase
{
    #[Test]
    public function canGetName(): void
    {
        $expectedName = DescriptionType::NAME;
        $actualName = new DescriptionType()->getName();

        $this->assertEquals($expectedName, $actualName);
    }

    #[Test]
    public function canConvertToPHPValueDescriptionToDescription(): void
    {
        $expected = $this->createStub(Description::class);

        $abstractPlatform = $this->createStub(AbstractPlatform::class);
        $actual = new DescriptionType()->convertToPHPValue($expected, $abstractPlatform);

        $this->assertSame($expected, $actual);
    }

    #[Test]
    public function canConvertToPHPValueNullToEmptyDescription(): void
    {
        $expected = '';

        $abstractPlatform = $this->createStub(AbstractPlatform::class);
        $actual = new DescriptionType()->convertToPHPValue(null, $abstractPlatform);

        $this->assertSame($expected, $actual->__toString());
    }

    #[Test]
    public function canConvertToPHPValueStringToDescription(): void
    {
        $expected = 'Awesome Description';

        $abstractPlatform = $this->createStub(AbstractPlatform::class);
        $actual = new DescriptionType()->convertToPHPValue($expected, $abstractPlatform);

        $this->assertSame($expected, $actual->__toString());
    }

    #[Test]
    public function canConvertToDatabaseValueObjectToString(): void
    {
        $expected = 'Awesome Description';

        $description = $this->createMock(Description::class);
        $description
            ->expects($this->once())
            ->method('__toString')
            ->willReturn($expected);

        $abstractPlatform = $this->createStub(AbstractPlatform::class);
        $actual = new DescriptionType()->convertToDatabaseValue($description, $abstractPlatform);

        $this->assertSame($expected, $actual);
    }

    #[Test]
    public function canConvertToDatabaseValueStringToString(): void
    {
        $expected = 'Awesome Description';

        $abstractPlatform = $this->createStub(AbstractPlatform::class);
        $actual = new DescriptionType()->convertToDatabaseValue($expected, $abstractPlatform);

        $this->assertSame($expected, $actual);
    }
}
