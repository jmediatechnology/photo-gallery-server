<?php

namespace App\Presentation\Action\Photograph;

use App\Application\Query\Photograph\GetQuery;
use App\Presentation\DTO\Photograph\GetAllInputDTO;
use App\Presentation\DTO\Photograph\OutputDTO;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

class GetAllAction
{
    public function __construct(private MessageBusInterface $bus) {}

    /**
     * @throws ExceptionInterface
     */
    public function __invoke(GetAllInputDTO $dto): JsonResponse
    {
        $envelope = $this->bus->dispatch(new GetQuery(
            title: $dto->title(),
        ));

        $handled = $envelope->last(HandledStamp::class);
        $photographs = $handled?->getResult();

        $outputDTOs = [];
        foreach ($photographs as $photograph) {
            $outputDTO = new OutputDTO(
                uuid: $photograph->uuid()?->__toString(),
                title: $photograph->title()->__toString(),
                description: $photograph->description()?->__toString(),
                filePath: $photograph->filePath()?->__toString(),
                createdAt: $photograph->createdAt()->format('Y-m-d H:i:s'),
                updatedAt: $photograph->updatedAt()->format('Y-m-d H:i:s'),
            );

            $outputDTOs[] = $outputDTO;
        }

        return new JsonResponse($outputDTOs);
    }
}
