<?php

namespace App\Tests\Unit\Infrastructure\Doctrine\Types;

use App\Domain\ValueObject\Title;
use App\Infrastructure\Doctrine\Types\TitleType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class TitleTypeTest extends TestCase
{
    #[Test]
    public function canGetName(): void
    {
        $expectedName = TitleType::NAME;
        $actualName = new TitleType()->getName();

        $this->assertEquals($expectedName, $actualName);
    }

    #[Test]
    public function throwsInvalidArgumentExceptionInCaseOfConvertingToPHPValueByPassingAnNull(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to convert a null value to Title.');

        $abstractPlatform = $this->createStub(AbstractPlatform::class);
        new TitleType()->convertToPHPValue(null, $abstractPlatform);
    }

    #[Test]
    public function canConvertToPHPValueTitleToTitle(): void
    {
        $expected = $this->createStub(Title::class);

        $abstractPlatform = $this->createStub(AbstractPlatform::class);
        $actual = new TitleType()->convertToPHPValue($expected, $abstractPlatform);

        $this->assertSame($expected, $actual);
    }

    #[Test]
    public function canConvertToPHPValueStringToTitle(): void
    {
        $expected = 'Awesome Title';

        $abstractPlatform = $this->createStub(AbstractPlatform::class);
        $actual = new TitleType()->convertToPHPValue($expected, $abstractPlatform);

        $this->assertSame($expected, $actual->__toString());
    }

    #[Test]
    public function canConvertToDatabaseValueObjectToString(): void
    {
        $expected = 'Awesome value string';

        $uuid = $this->createMock(Title::class);
        $uuid
            ->expects($this->once())
            ->method('__toString')
            ->willReturn($expected);

        $abstractPlatform = $this->createStub(AbstractPlatform::class);
        $actual = new TitleType()->convertToDatabaseValue($uuid, $abstractPlatform);

        $this->assertSame($expected, $actual);
    }

    #[Test]
    public function canConvertToDatabaseValueStringToString(): void
    {
        $expected = 'Awesome value string';

        $abstractPlatform = $this->createStub(AbstractPlatform::class);
        $actual = new TitleType()->convertToDatabaseValue($expected, $abstractPlatform);

        $this->assertSame($expected, $actual);
    }
}
