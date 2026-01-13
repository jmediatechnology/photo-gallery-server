<?php

namespace App\Tests\Unit\Domain\ValueObject;

use App\Domain\ValueObject\Title;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class TitleTest extends TestCase
{
    #[Test]
    public function canCreate(): void
    {
        $string = 'Awesome Title';
        $title = new Title($string);

        $this->assertSame($string, $title-> __toString());
    }

    #[Test]
    public function throwsInvalidArgumentExceptionInCaseOfExceedingMaxLength(): void
    {
        $string = str_repeat('.', Title::MAX_LENGTH + 1);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Max length exceeded');

        new Title($string);
    }
}
