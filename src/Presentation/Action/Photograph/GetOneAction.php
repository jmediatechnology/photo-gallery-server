<?php

namespace App\Presentation\Action\Photograph;

use App\Domain\Entity\Photograph;
use App\Presentation\DTO\Photograph\OutputDTO;
use Symfony\Component\HttpFoundation\JsonResponse;

class GetOneAction
{
    public function __invoke(Photograph $photograph): JsonResponse
    {
        $photographOutputDTO = new OutputDTO(
            uuid: $photograph->uuid()?->__toString(),
            title: $photograph->title()->__toString(),
            description: $photograph->description()?->__toString(),
            filePath: $photograph->filePath()?->__toString(),
            createdAt: $photograph->createdAt()->format('Y-m-d H:i:s'),
            updatedAt: $photograph->updatedAt()->format('Y-m-d H:i:s'),
        );

        return new JsonResponse($photographOutputDTO);
    }
}
