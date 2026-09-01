<?php

namespace App\Tests\Unit\Presentation\Resolver\Photograph;

use App\Presentation\Resolver\Photograph\UuidInputDTOResolver;
use Generator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class UuidInputDTOResolverTest extends TestCase
{
    #[Test]
    public function resolvesToEmptyArrayWhenArgumentTypeIsNotUuidInputDTO(): void
    {
        $title = 'awesome title';

        $request = Request::create(uri: '/');
        $request->initialize(
            request: ['title' => $title],
            server: ['CONTENT_TYPE' => 'application/x-www-form-urlencoded'],
        );

        $argument = $this->createMock(ArgumentMetadata::class);
        $argument
            ->expects($this->once())
            ->method('getType')
            ->willReturn(null);

        /** @var $iterable Generator */
        $iterable = new UuidInputDTOResolver()->resolve($request, $argument);

        $actual = iterator_to_array($iterable);

        self::assertEmpty($actual);
    }
}
