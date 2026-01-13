<?php

declare(strict_types=1);

namespace App\Infrastructure\Symfony\Event;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ExceptionEventListener
{
    public function __invoke(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if (!$exception instanceof HttpExceptionInterface) {
            return;
        }

        $response = new JsonResponse(
            data: [
                'errors' => $exception->getMessage(),
            ],
            status: $exception->getStatusCode()
        );
        $event->setResponse($response);
    }
}
