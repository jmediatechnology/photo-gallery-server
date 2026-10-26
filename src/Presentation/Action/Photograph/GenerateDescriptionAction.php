<?php

namespace App\Presentation\Action\Photograph;

use App\Application\Query\Photograph\GenerateDescriptionForKnownPhotographUuidQuery;
use App\Domain\Enum\ImageDescriptionGenerationFailureReason;
use App\Domain\Exception\ImageDescriptionGenerationFailureException;
use App\Presentation\DTO\Photograph\UuidInputDTO;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
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
     * @throws HttpExceptionInterface
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

        try {
            $envelope = $this->bus->dispatch($query);
        } catch (HandlerFailedException $exception) {

            /** @var ImageDescriptionGenerationFailureException|null $failureException */
            $failureException = array_first($exception->getWrappedExceptions(ImageDescriptionGenerationFailureException::class));
            if ($failureException === null) {
                throw $exception;
            }

            throw match ($failureException->reason) {
                ImageDescriptionGenerationFailureReason::ImageTooLarge => new UnprocessableEntityHttpException(
                    message: 'This photograph is too large to generate a description for.',
                    previous: $failureException,
                ),
                ImageDescriptionGenerationFailureReason::ProviderBusy,
                ImageDescriptionGenerationFailureReason::ProviderUnavailable => new ServiceUnavailableHttpException(
                    retryAfter: $failureException->retryAfter ?? 30,
                    message: 'Description service unavailable, try again later.',
                    previous: $failureException,
                ),
            };
        }

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
