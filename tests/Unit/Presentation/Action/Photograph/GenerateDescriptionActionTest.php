<?php

namespace App\Tests\Unit\Presentation\Action\Photograph;

use App\Application\Query\Photograph\GenerateDescriptionForKnownPhotographUuidQuery;
use App\Presentation\Action\Photograph\GenerateDescriptionAction;
use App\Presentation\DTO\Photograph\UuidInputDTO;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class GenerateDescriptionActionTest extends TestCase
{
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
}
