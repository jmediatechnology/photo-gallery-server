<?php

namespace App\Presentation\Action\Photograph;

use App\Application\Commands\Photograph\CreateCommand;
use App\Domain\Entity\Photograph;
use App\Presentation\DTO\Photograph\InputDTO;
use App\Presentation\DTO\Photograph\OutputDTO;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[IsGranted('ROLE_ADMIN', statusCode: 423)]
class CreateAction
{
    public function __construct(
        private MessageBusInterface $bus,
        private ValidatorInterface $validator,
    ) {}

    /**
     * @throws ExceptionInterface
     */
    public function __invoke(InputDTO $dto): JsonResponse
    {
        $createCommand = new CreateCommand(
            uuid: $dto->uuid(),
            title: $dto->title(),
            description: $dto->description(),
            file: $dto->file(),
        );

        $violations = $this->validator->validate($createCommand);
        if (count($violations) > 0) {
            return $this->buildJsonResponseForViolations($violations);
        }

        $envelope = $this->bus->dispatch($createCommand);
        $handled = $envelope->last(HandledStamp::class);
        $photograph = $handled?->getResult();

        if (!$photograph instanceof Photograph) {
            return new JsonResponse(
                data: ['errors' => 'Something went wrong while creating the photograph.'],
                status: Response::HTTP_CONFLICT,
            );
        }

        return new JsonResponse(new OutputDTO(
            uuid: $photograph->uuid()?->__toString(),
            title: $photograph->title()->__toString(),
            description: $photograph->description()?->__toString(),
            filePath: $photograph->filePath()?->__toString(),
            createdAt: $photograph->createdAt()->format('Y-m-d H:i:s'),
            updatedAt: $photograph->updatedAt()->format('Y-m-d H:i:s'),
        ));
    }

    private function buildJsonResponseForViolations(ConstraintViolationListInterface $violations): JsonResponse
    {
        $violationMessages = [];
        foreach ($violations as $violation) {
            $violationMessages[] = $violation->getMessage();
        }

        return new JsonResponse(
            data: ['errors' => array_reduce(
                array: $violationMessages,
                callback: static function($carry, string $item) {
                    return $carry . $item . '.';
                },
                initial: '',
            )],
            status: 422,
        );
    }
}
