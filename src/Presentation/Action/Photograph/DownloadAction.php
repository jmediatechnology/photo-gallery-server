<?php

namespace App\Presentation\Action\Photograph;

use App\Application\Query\Photograph\DownloadQuery;
use App\Application\ValueObject\PhotographArchive;
use App\Presentation\Attribute\RequiresDownloadSlot;
use App\Presentation\DTO\Photograph\DownloadInputDTO;
use App\Presentation\Response\ArchiveResponseFactory;
use App\Presentation\Response\JsonErrorResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\RateLimit;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use ZipStream\Exception as ZipStreamException;

#[RateLimit('photograph_download')]
#[RequiresDownloadSlot]
readonly class DownloadAction
{
    public function __construct(
        private MessageBusInterface    $bus,
        private ValidatorInterface     $validator,
        private ArchiveResponseFactory $archiveResponseFactory,
    ) {}

    /**
     * @throws ExceptionInterface
     * @throws ZipStreamException
     */
    public function __invoke(DownloadInputDTO $dto): Response
    {
        $query = new DownloadQuery(
            uuids: $dto->uuids(),
        );

        $violations = $this->validator->validate($query);
        if (count($violations) > 0) {
            return JsonErrorResponse::fromViolations($violations);
        }

        $envelope = $this->bus->dispatch($query);
        $handled = $envelope->last(HandledStamp::class);
        $photographArchive = $handled?->getResult();

        if (!$photographArchive instanceof PhotographArchive) {
            return new JsonErrorResponse(
                message: 'Something went wrong while preparing the download.',
                status: Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }

        return $this->archiveResponseFactory->create($photographArchive);
    }
}
