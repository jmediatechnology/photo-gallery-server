<?php

namespace App\Tests\Unit\Domain\ValueObject;

use App\Domain\ValueObject\FilePath;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class FilePathTest extends TestCase
{
    #[Test]
    public function canCreate(): void
    {
        $string = '/public/images/image.jpg';
        $title = new FilePath($string);

        $this->assertSame($string, $title-> __toString());
    }

    #[Test]
    public function throwsInvalidArgumentExceptionInCaseOfExceedingMaxLength(): void
    {
        $string = str_repeat('.', FilePath::MAX_LENGTH + 1);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Max length exceeded');

        new FilePath($string);
    }
}
