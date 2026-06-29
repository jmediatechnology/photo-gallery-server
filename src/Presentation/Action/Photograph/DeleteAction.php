<?php

namespace App\Presentation\Action\Photograph;

use App\Application\Commands\Photograph\DeleteCommand;
use App\Domain\Entity\Photograph;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN', statusCode: 423)]
class DeleteAction
{
    public function __construct(private MessageBusInterface $bus) {}

    public function __invoke(Photograph $photograph): JsonResponse
    {
        $this->bus->dispatch(new DeleteCommand(
            photograph: $photograph,
        ));

        return new JsonResponse(
            data: '',
            status: 204,
        );
    }
}
