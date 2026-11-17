<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Symfony\Event;

use App\Domain\Enum\ImageDescriptionGenerationFailureReason;
use App\Domain\Exception\ImageDescriptionGenerationFailureException;
use App\Infrastructure\Symfony\Event\MessengerExceptionListener;
use App\Presentation\Factory\ImageDescriptionFailureHttpExceptionFactory;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Throwable;

class MessengerExceptionListenerTest extends TestCase
{
    private MessengerExceptionListener $listener;

    protected function setUp(): void
    {
        $this->listener = new MessengerExceptionListener(
            new ImageDescriptionFailureHttpExceptionFactory(),
        );
    }

    #[Test]
    public function convertsWrappedFailureToHttpException(): void
    {
        $failureException = $this->createFailureException();
        $exceptionEvent = $this->createExceptionEvent($this->createHandlerFailedException($failureException));

        $this->listener->__invoke($exceptionEvent);

        $throwable = $exceptionEvent->getThrowable();
        $this->assertInstanceOf(UnprocessableEntityHttpException::class, $throwable);
        $this->assertSame($failureException, $throwable->getPrevious());
    }

    #[Test]
    public function findsFailureBetweenOtherWrappedExceptions(): void
    {
        $failureException = $this->createFailureException();
        $exceptionEvent = $this->createExceptionEvent($this->createHandlerFailedException(new RuntimeException(), $failureException));

        $this->listener->__invoke($exceptionEvent);

        $this->assertInstanceOf(UnprocessableEntityHttpException::class, $exceptionEvent->getThrowable());
    }

    #[Test]
    public function ignoresHandlerFailedExceptionWithoutFailure(): void
    {
        $handlerFailedException = $this->createHandlerFailedException(new RuntimeException());
        $exceptionEvent = $this->createExceptionEvent($handlerFailedException);

        $this->listener->__invoke($exceptionEvent);

        $this->assertSame($handlerFailedException, $exceptionEvent->getThrowable());
    }

    #[Test]
    public function ignoresOtherExceptions(): void
    {
        $exception = new RuntimeException();
        $exceptionEvent = $this->createExceptionEvent($exception);

        $this->listener->__invoke($exceptionEvent);

        $this->assertSame($exception, $exceptionEvent->getThrowable());
    }

    #[Test]
    public function ignoresUnwrappedFailure(): void
    {
        $failureException = $this->createFailureException();
        $exceptionEvent = $this->createExceptionEvent($failureException);

        $this->listener->__invoke($exceptionEvent);

        $this->assertSame($failureException, $exceptionEvent->getThrowable());
    }

    private function createFailureException(): ImageDescriptionGenerationFailureException
    {
        return new ImageDescriptionGenerationFailureException(
            reason: ImageDescriptionGenerationFailureReason::ImageTooLarge,
        );
    }

    private function createHandlerFailedException(Throwable ...$exceptions): HandlerFailedException
    {
        return new HandlerFailedException(new Envelope(new stdClass()), $exceptions);
    }

    private function createExceptionEvent(Throwable $throwable): ExceptionEvent
    {
        return new ExceptionEvent(
            $this->createStub(HttpKernelInterface::class),
            new Request(),
            HttpKernelInterface::MAIN_REQUEST,
            $throwable,
        );
    }
}

