<?php

namespace App\Presentation\Action\Photograph;

use App\Application\Commands\Photograph\UpdateCommand;
use App\Domain\Entity\Photograph;
use App\Presentation\DTO\Photograph\OutputDTO;
use App\Presentation\DTO\Photograph\UpdateInputDTO;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

class UpdateAction
{
    public function __construct(private MessageBusInterface $bus) {}

    public function __invoke(
        Photograph $photograph,
        #[MapRequestPayload] UpdateInputDTO $dto
    ): JsonResponse {

        $updateCommand = new UpdateCommand(
            photograph: $photograph,
            title: $dto->title(),
            description: $dto->description(),
        );

        // @todo validate update command

        $envelope = $this->bus->dispatch($updateCommand);

        $handled = $envelope->last(HandledStamp::class);
        $photograph = $handled?->getResult();

        $outputDTO = new OutputDTO(
            uuid: $photograph->uuid()?->__toString(),
            title: $photograph->title()->__toString(),
            description: $photograph->description()?->__toString(),
            filePath: $photograph->filePath()->__toString(),
            createdAt: $photograph->createdAt()->format('Y-m-d H:i:s'),
            updatedAt: $photograph->updatedAt()->format('Y-m-d H:i:s'),
        );

        return new JsonResponse($outputDTO);
    }
}
