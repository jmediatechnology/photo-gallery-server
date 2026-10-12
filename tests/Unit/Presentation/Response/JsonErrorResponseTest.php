<?php

declare(strict_types=1);

namespace App\Tests\Unit\Presentation\Response;

use App\Presentation\Response\JsonErrorResponse;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

final class JsonErrorResponseTest extends TestCase
{
    #[Test]
    public function wrapsTheMessageInAnErrorsField(): void
    {
        $response = new JsonErrorResponse(message: 'Not found', status: 404, headers: ['Retry-After' => '30']);

        self::assertSame(404, $response->getStatusCode());
        self::assertSame('{"errors":"Not found"}', $response->getContent());
        self::assertSame('30', $response->headers->get('Retry-After'));
    }

    #[Test]
    public function joinsViolationMessagesIntoOneUnprocessableContentResponse(): void
    {
        $violations = new ConstraintViolationList([
            $this->createViolation('First problem'),
            $this->createViolation('Second problem'),
        ]);

        $response = JsonErrorResponse::fromViolations($violations);

        self::assertSame(422, $response->getStatusCode());
        self::assertSame('{"errors":"First problem. Second problem. "}', $response->getContent());
    }

    private function createViolation(string $message): ConstraintViolation
    {
        return new ConstraintViolation(
            message: $message,
            messageTemplate: null,
            parameters: [],
            root: null,
            propertyPath: '',
            invalidValue: null,
        );
    }
}
