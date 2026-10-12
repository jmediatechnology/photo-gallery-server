<?php

namespace App\Presentation\Response;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * The API's error body, {"errors": "..."}, as also produced by ExceptionEventListener.
 */
class JsonErrorResponse extends JsonResponse
{
    /**
     * @param array<string, string> $headers
     */
    public function __construct(string $message, int $status, array $headers = [])
    {
        parent::__construct(
            data: ['errors' => $message],
            status: $status,
            headers: $headers,
        );
    }

    public static function fromViolations(ConstraintViolationListInterface $violations): self
    {
        $message = '';
        foreach ($violations as $violation) {
            $message .= $violation->getMessage() . '. ';
        }

        return new self(
            message: $message,
            status: Response::HTTP_UNPROCESSABLE_ENTITY,
        );
    }
}
