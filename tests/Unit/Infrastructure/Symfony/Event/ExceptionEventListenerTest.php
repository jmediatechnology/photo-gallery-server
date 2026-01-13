<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Symfony\Event;

use App\Infrastructure\Symfony\Event\ExceptionEventListener;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

class ExceptionEventListenerTest extends TestCase
{
    #[Test]
    public function doesNothingWhenExceptionEventIsNotInstanceOfHttpExceptionInterface(): void
    {
        $exceptionEvent = $this->createStub(ExceptionEvent::class);
        new ExceptionEventListener()->__invoke($exceptionEvent);

        $this->assertTrue(true);
    }
}
