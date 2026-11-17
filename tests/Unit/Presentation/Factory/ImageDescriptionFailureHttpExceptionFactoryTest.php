<?php

declare(strict_types=1);

namespace App\Tests\Unit\Presentation\Factory;

use App\Domain\Enum\ImageDescriptionGenerationFailureReason;
use App\Domain\Exception\ImageDescriptionGenerationFailureException;
use App\Presentation\Factory\ImageDescriptionFailureHttpExceptionFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class ImageDescriptionFailureHttpExceptionFactoryTest extends TestCase
{
    private ImageDescriptionFailureHttpExceptionFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new ImageDescriptionFailureHttpExceptionFactory();
    }

    #[Test]
    public function createsUnprocessableEntityForImageTooLarge(): void
    {
        $exception = new ImageDescriptionGenerationFailureException(
            reason: ImageDescriptionGenerationFailureReason::ImageTooLarge,
        );

        $httpException = $this->factory->create($exception);

        $this->assertInstanceOf(UnprocessableEntityHttpException::class, $httpException);
        $this->assertSame($exception, $httpException->getPrevious());
    }

    #[Test]
    #[DataProvider('providerFailureReasons')]
    public function createsServiceUnavailableWithProvidedRetryAfter(ImageDescriptionGenerationFailureReason $reason): void
    {
        $exception = new ImageDescriptionGenerationFailureException(
            reason: $reason,
            retryAfter: 120,
        );

        $httpException = $this->factory->create($exception);

        $this->assertInstanceOf(ServiceUnavailableHttpException::class, $httpException);
        $this->assertSame(['Retry-After' => 120], $httpException->getHeaders());
        $this->assertSame($exception, $httpException->getPrevious());
    }

    #[Test]
    #[DataProvider('providerFailureReasons')]
    public function fallsBackToDefaultRetryAfter(ImageDescriptionGenerationFailureReason $reason): void
    {
        $exception = new ImageDescriptionGenerationFailureException(
            reason: $reason,
            retryAfter: null,
        );

        $httpException = $this->factory->create($exception);

        $this->assertSame(['Retry-After' => 30], $httpException->getHeaders());
    }

    /**
     * @return iterable<string, array{ImageDescriptionGenerationFailureReason}>
     */
    public static function providerFailureReasons(): iterable
    {
        yield 'provider busy' => [ImageDescriptionGenerationFailureReason::ProviderBusy];
        yield 'provider unavailable' => [ImageDescriptionGenerationFailureReason::ProviderUnavailable];
    }
}
