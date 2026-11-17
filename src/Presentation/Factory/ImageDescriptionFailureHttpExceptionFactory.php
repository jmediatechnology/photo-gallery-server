<?php

namespace App\Presentation\Factory;

use App\Domain\Enum\ImageDescriptionGenerationFailureReason;
use App\Domain\Exception\ImageDescriptionGenerationFailureException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class ImageDescriptionFailureHttpExceptionFactory
{
    private const int DEFAULT_RETRY_AFTER_SECONDS = 30;

    public function create(ImageDescriptionGenerationFailureException $exception): HttpException
    {
        return match ($exception->reason) {
            ImageDescriptionGenerationFailureReason::ImageTooLarge => new UnprocessableEntityHttpException(
                message: 'This photograph is too large to generate a description for.',
                previous: $exception,
            ),
            ImageDescriptionGenerationFailureReason::ProviderBusy,
            ImageDescriptionGenerationFailureReason::ProviderUnavailable => new ServiceUnavailableHttpException(
                retryAfter: $exception->retryAfter ?? self::DEFAULT_RETRY_AFTER_SECONDS,
                message: 'Description service unavailable, try again later.',
                previous: $exception,
            ),
        };
    }
}
