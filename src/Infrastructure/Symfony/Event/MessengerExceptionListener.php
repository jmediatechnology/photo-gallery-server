<?php

namespace App\Infrastructure\Symfony\Event;

use App\Domain\Exception\ImageDescriptionGenerationFailureException;
use App\Presentation\Factory\ImageDescriptionFailureHttpExceptionFactory;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\Messenger\Exception\HandlerFailedException;

class MessengerExceptionListener
{
    public function __construct(
        private ImageDescriptionFailureHttpExceptionFactory $httpExceptionFactory,
    ) {}

    public function __invoke(ExceptionEvent $event): void
    {
        $throwable = $event->getThrowable();
        if (!$throwable instanceof HandlerFailedException) {
            return;
        }

        /** @var ImageDescriptionGenerationFailureException|null $failureException */
        $failureException = array_first($throwable->getWrappedExceptions(ImageDescriptionGenerationFailureException::class));

        if (!$failureException instanceof ImageDescriptionGenerationFailureException) {
            return;
        }

        $httpException = $this->httpExceptionFactory->create($failureException);
        $event->setThrowable($httpException);
    }
}
