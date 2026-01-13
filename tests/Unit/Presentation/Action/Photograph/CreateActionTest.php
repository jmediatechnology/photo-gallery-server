<?php

namespace App\Tests\Unit\Presentation\Action\Photograph;

use App\Application\Commands\Photograph\CreateCommand;
use App\Presentation\Action\Photograph\CreateAction;
use App\Presentation\DTO\Photograph\InputDTO;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CreateActionTest extends TestCase
{
    /**
     * @throws ExceptionInterface
     */
    #[Test]
    public function returnsSomethingWentWrongWhenValueIsNotInstanceOfChangePhotographStatus(): void
    {
        $envelope = $this->createMock(Envelope::class);
        $envelope
            ->expects($this->once())
            ->method('last')
            ->with(HandledStamp::class)
            ->willReturn(null);

        $file = $this->createStub(UploadedFile::class);

        $bus = $this->createMock(MessageBusInterface::class);
        $bus
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(CreateCommand::class))
            ->willReturn($envelope);

        $dto = $this->createMock(InputDTO::class);
        $dto
            ->expects($this->once())
            ->method('uuid')
            ->willReturn('305a85de-f2dc-46d2-a66a-ab59ad737390');

        $dto
            ->expects($this->once())
            ->method('title')
            ->willReturn('Awesome Title');

        $dto
            ->expects($this->once())
            ->method('description')
            ->willReturn('Awesome Description');

        $dto
            ->expects($this->once())
            ->method('file')
            ->willReturn($file);

        $validator = $this->createStub(ValidatorInterface::class);

        $actual = new CreateAction($bus, $validator)->__invoke($dto);

        $this->assertInstanceOf(JsonResponse::class, $actual);
        self::assertStringContainsString('Something went wrong while creating the photograph.', $actual->getContent());
    }
}
