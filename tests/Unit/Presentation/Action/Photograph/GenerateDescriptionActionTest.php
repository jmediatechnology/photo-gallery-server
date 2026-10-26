<?php

namespace App\Tests\Unit\Presentation\Action\Photograph;

use App\Application\Handler\Photograph\GenerateDescriptionHandler;
use App\Application\Query\Photograph\GenerateDescriptionForKnownPhotographUuidQuery;
use App\Domain\Enum\ImageDescriptionGenerationFailureReason;
use App\Domain\Exception\ImageDescriptionGenerationFailureException;
use App\Presentation\Action\Photograph\GenerateDescriptionAction;
use App\Presentation\DTO\Photograph\UuidInputDTO;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Throwable;
use UnexpectedValueException;

class GenerateDescriptionActionTest extends TestCase
{
    private const string UUID = 'f619a6e7-c5c8-47fb-bfdb-c426f998471c';

    /**
     * @throws ExceptionInterface
     */
    #[Test]
    public function returnsSomethingWentWrongWhenValueIsNotInstanceOfChangePhotographStatus(): void
    {
        $dto = $this->createMock(UuidInputDTO::class);
        $dto
            ->expects($this->once())
            ->method('uuid')
            ->willReturn('f619a6e7-c5c8-47fb-bfdb-c426f998471c');

        $envelope = $this->createMock(Envelope::class);
        $envelope
            ->expects($this->once())
            ->method('last')
            ->with(HandledStamp::class)
            ->willReturn(null);

        $bus = $this->createMock(MessageBusInterface::class);
        $bus
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(GenerateDescriptionForKnownPhotographUuidQuery::class))
            ->willReturn($envelope);

        $validator = $this->createStub(ValidatorInterface::class);

        $actual = new GenerateDescriptionAction($bus, $validator)->__invoke($dto);

        $this->assertInstanceOf(JsonResponse::class, $actual);
        self::assertStringContainsString('Something went wrong while generating the description.', $actual->getContent());
    }

    /**
     * @throws ExceptionInterface
     */
    #[Test]
    public function returnsJsonResponseContainingViolations(): void
    {
        $dto = $this->createMock(UuidInputDTO::class);
        $dto
            ->expects($this->once())
            ->method('uuid')
            ->willReturn('bec90c55-c44e-4181-be91-58656742d95b');

        $bus = $this->createMock(MessageBusInterface::class);
        $bus
            ->expects($this->never())
            ->method('dispatch');

        $violations = new ConstraintViolationList([
            new ConstraintViolation(
                message: 'Custom violation message',
                messageTemplate: null,
                parameters: [],
                root: null,
                propertyPath: '',
                invalidValue: null,
            ),
        ]);

        $validator = $this->createMock(ValidatorInterface::class);
        $validator
            ->expects($this->once())
            ->method('validate')
            ->with($this->isInstanceOf(GenerateDescriptionForKnownPhotographUuidQuery::class))
            ->willReturn($violations);

        $actual = new GenerateDescriptionAction($bus, $validator)->__invoke($dto);

        $this->assertInstanceOf(JsonResponse::class, $actual);
        self::assertStringContainsString('Custom violation message. ', $actual->getContent());
    }

    /**
     * @param class-string<HttpExceptionInterface> $expectedExceptionClass
     * @param array<string, int>                   $expectedHeaders
     *
     * @throws ExceptionInterface
     */
    #[Test]
    #[DataProvider('provideImageDescriptionFailures')]
    public function translatesTheImageDescriptionFailureIntoAnHttpException(
        ImageDescriptionGenerationFailureReason $reason,
        ?int $retryAfter,
        string $expectedExceptionClass,
        int $expectedStatusCode,
        array $expectedHeaders,
        string $expectedMessage,
    ): void {
        $failureException = new ImageDescriptionGenerationFailureException($reason, $retryAfter);
        $handlerFailedException = $this->createHandlerFailedException($failureException);
        $bus = $this->createBusFailingWith($handlerFailedException);
        $validator = $this->createStub(ValidatorInterface::class);

        try {

            new GenerateDescriptionAction(
                bus: $bus,
                validator: $validator,
            )->__invoke(new UuidInputDTO(self::UUID));

            self::fail(sprintf('Expected %s to be thrown.', $expectedExceptionClass));

        } catch (HttpExceptionInterface $exception) {
            self::assertInstanceOf($expectedExceptionClass, $exception);
            self::assertSame($expectedStatusCode, $exception->getStatusCode());
            self::assertSame($expectedHeaders, $exception->getHeaders());
            self::assertSame($expectedMessage, $exception->getMessage());
            self::assertSame($failureException, $exception->getPrevious());
        }
    }

    /**
     * @throws ExceptionInterface|HttpExceptionInterface
     */
    #[Test]
    public function rethrowsHandlerFailuresThatAreNotAnImageDescriptionFailure(): void
    {
        $failureException = new UnexpectedValueException('Photograph not found');
        $handlerFailedException = $this->createHandlerFailedException($failureException);
        $bus = $this->createBusFailingWith($handlerFailedException);
        $validator = $this->createStub(ValidatorInterface::class);

        try {

            new GenerateDescriptionAction(
                bus: $bus,
                validator: $validator,
            )->__invoke(new UuidInputDTO(self::UUID));

            self::fail(sprintf('Expected %s to be rethrown.', HandlerFailedException::class));

        } catch (HandlerFailedException $exception) {
            self::assertSame($handlerFailedException, $exception);
        }
    }

    /**
     * Messenger keys the wrapped exceptions by handler name, not by 0. Building it the same way here
     * keeps the test honest: destructuring with [$failure] = ... would not find the failure.
     */
    private function createHandlerFailedException(Throwable $handlerException): HandlerFailedException
    {
        return new HandlerFailedException(
            new Envelope(new GenerateDescriptionForKnownPhotographUuidQuery(uuid: self::UUID)),
            [GenerateDescriptionHandler::class . '::__invoke' => $handlerException],
        );
    }

    private function createBusFailingWith(HandlerFailedException $handlerFailedException): MessageBusInterface
    {
        $bus = $this->createStub(MessageBusInterface::class);
        $bus
            ->method('dispatch')
            ->willThrowException($handlerFailedException);

        return $bus;
    }

    /**
     * @return iterable<string, array{ImageDescriptionGenerationFailureReason, ?int, class-string<HttpExceptionInterface>, int, array<string, int>, string}>
     */
    public static function provideImageDescriptionFailures(): iterable
    {
        yield 'image too large' => [
            ImageDescriptionGenerationFailureReason::ImageTooLarge,
            null,
            UnprocessableEntityHttpException::class,
            422,
            [],
            'This photograph is too large to generate a description for.',
        ];

        yield 'provider busy, with retry-after from the provider' => [
            ImageDescriptionGenerationFailureReason::ProviderBusy,
            12,
            ServiceUnavailableHttpException::class,
            503,
            ['Retry-After' => 12],
            'Description service unavailable, try again later.',
        ];

        yield 'provider busy, without retry-after from the provider' => [
            ImageDescriptionGenerationFailureReason::ProviderBusy,
            null,
            ServiceUnavailableHttpException::class,
            503,
            ['Retry-After' => 30],
            'Description service unavailable, try again later.',
        ];

        yield 'provider unavailable' => [
            ImageDescriptionGenerationFailureReason::ProviderUnavailable,
            null,
            ServiceUnavailableHttpException::class,
            503,
            ['Retry-After' => 30],
            'Description service unavailable, try again later.',
        ];
    }
}
