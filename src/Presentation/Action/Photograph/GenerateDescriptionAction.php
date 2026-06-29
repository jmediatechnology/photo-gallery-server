<?php

namespace App\Presentation\Action\Photograph;

use App\Application\Query\Photograph\GenerateDescriptionForKnownPhotographUuidQuery;
use App\Domain\Entity\Photograph;
use App\Presentation\DTO\Photograph\DescriptionOutputDTO;
use App\Presentation\DTO\Photograph\UuidInputDTO;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[IsGranted('ROLE_ADMIN', statusCode: 423)]
class GenerateDescriptionAction
{
    public function __construct(
        private MessageBusInterface $bus,
        private ValidatorInterface $validator,
    ) {}

    /**
     * @throws ExceptionInterface
     */
    public function __invoke(UuidInputDTO $dto): JsonResponse
    {
        $query = new GenerateDescriptionForKnownPhotographUuidQuery(
            uuid: $dto->uuid(),
        );

        $violations = $this->validator->validate($query);
        if (count($violations) > 0) {
            return $this->buildJsonResponseForViolations($violations);
        }

        $envelope = $this->bus->dispatch($query);
        $handled = $envelope->last(HandledStamp::class);
        $description = $handled?->getResult();

        if (!is_string($description)) {
            return new JsonResponse(
                data: ['errors' => 'Something went wrong while generating the description.'],
                status: Response::HTTP_CONFLICT,
            );
        }

        return new JsonResponse([
            'description' => $description,
        ]);
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
                callback: static function(string $carry, string $violationMessage) {
                    return $carry . $violationMessage . '. ';
                },
                initial: '',
            )],
            status: 422,
        );
    }
}
