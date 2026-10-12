<?php

declare(strict_types=1);

namespace App\Infrastructure\Symfony\Event;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
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
                'errors' => $this->createMessage($exception),
            ],
            status: $exception->getStatusCode(),
            headers: $exception->getHeaders(),
        );
        $event->setResponse($response);
    }

    /**
     * Framework exceptions such as the one thrown by #[RateLimit] carry no message;
     * fall back to the standard reason phrase, e.g. "Too Many Requests".
     */
    private function createMessage(HttpExceptionInterface $exception): string
    {
        $message = $exception->getMessage();
        if ($message !== '') {
            return $message;
        }

        return Response::$statusTexts[$exception->getStatusCode()] ?? '';
    }
}
