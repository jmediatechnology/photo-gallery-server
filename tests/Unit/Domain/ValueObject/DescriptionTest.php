<?php

namespace App\Tests\Unit\Domain\ValueObject;

use App\Domain\ValueObject\Description;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class DescriptionTest extends TestCase
{
    #[Test]
    public function canCreate(): void
    {
        $string = 'Awesome Description';
        $description = new Description($string);

        $this->assertSame($string, $description-> __toString());
    }
}
