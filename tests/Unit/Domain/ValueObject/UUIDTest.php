<?php

use App\Domain\ValueObject\UUID;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class UUIDTest extends TestCase
{
    #[Test]
    public function canCreate(): void
    {
        $string = 'd9e7a184-5d5b-11ea-a62a-3499710062d0';
        $uuid = new UUID($string);

        $this->assertSame($string, $uuid-> __toString());
    }

    #[Test]
    public function throwsInvalidArgumentExceptionInCaseOfInvalidUUID(): void
    {
        $string = 'invalid uuid';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid uuid provided: "invalid uuid"');

        new UUID($string);
    }
}
