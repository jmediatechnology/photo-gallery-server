<?php

namespace App\Tests\Unit\Presentation\Action\Photograph;

use App\Application\Commands\Photograph\CreateCommand;
use App\Application\Query\Photograph\DownloadQuery;
use App\Presentation\Action\Photograph\DownloadAction;
use App\Presentation\DTO\Photograph\DownloadInputDTO;
use App\Presentation\Response\ArchiveResponseFactory;
use App\Presentation\Response\JsonErrorResponse;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use ZipStream\Exception;

class DownloadActionTest extends TestCase
{
    /**
     * @throws ExceptionInterface
     * @throws Exception
     */
    #[Test]
    public function returnsSomethingWentWrongWhenDispatcherWasNotAbleToRetrieveAnInstanceOfPhotographArchive(): void
    {
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
            ->with($this->isInstanceOf(DownloadQuery::class))
            ->willReturn($envelope);

        $dto = $this->createMock(DownloadInputDTO::class);
        $dto
            ->expects($this->once())
            ->method('uuids')
            ->willReturn(['2265605a-3ccf-48e8-8edf-3366d2f2d19a', '8b88b486-a17e-4267-8a89-42f51c98af2c']);

        $constraintViolationList = $this->createMock(ConstraintViolationListInterface::class);
        $constraintViolationList
            ->expects($this->once())
            ->method('count')
            ->willReturn(0);

        $validator = $this->createMock(ValidatorInterface::class);
        $validator
            ->expects($this->once())
            ->method('validate')
            ->with($this->isInstanceOf(DownloadQuery::class))
            ->willReturn($constraintViolationList);

        $archiveResponseFactory = $this->createMock(ArchiveResponseFactory::class);
        $archiveResponseFactory
            ->expects($this->never())
            ->method('create');

        $actual = new DownloadAction($bus, $validator, $archiveResponseFactory)->__invoke($dto);

        $this->assertInstanceOf(JsonErrorResponse::class, $actual);
        self::assertStringContainsString('Something went wrong while preparing the download.', $actual->getContent());
    }
}
