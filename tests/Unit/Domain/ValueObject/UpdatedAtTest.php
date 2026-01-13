<?php

namespace App\Tests\Unit\Domain\ValueObject;

use App\Domain\ValueObject\UpdatedAt;
use DateMalformedStringException;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class UpdatedAtTest extends TestCase
{
    #[Test]
    public function canCreateFromDateTimeImmutable(): void
    {
        $string = '17-10-2025 18:00:00';
        $datetimeImmutable = new DateTimeImmutable($string);
        $updatedAt = new UpdatedAt($datetimeImmutable);

        $this->assertSame($string, $updatedAt-> __toString());
    }

    #[Test]
    public function canCreateFromString(): void
    {
        $string = '17-10-2025 18:00:00';
        $updatedAt = UpdatedAt::fromString($string);

        $this->assertSame($string, $updatedAt-> __toString());
    }

    #[Test]
    public function throwsInvalidArgumentExceptionInCaseOfDateMalformedString(): void
    {
        $string = 'malformed-date-string';

        $this->expectException(DateMalformedStringException::class);

        UpdatedAt::fromString($string);
    }

    #[Test]
    public function canFormat(): void
    {
        $string = '17-10-2025 18:00:00';
        $expected = '2025-10-17 18:00:00';

        $actual = UpdatedAt::fromString($string)->format('Y-m-d H:i:s');

        $this->assertSame($expected, $actual);
    }
}
