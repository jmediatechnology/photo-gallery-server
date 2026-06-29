<?php

namespace App\Tests\Unit\Presentation\Resolver\Photograph;

use App\Presentation\DTO\Photograph\GetAllInputDTO;
use App\Presentation\Resolver\Photograph\GetAllInputDTOResolver;
use Generator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class GetAllInputDTOResolverTest extends TestCase
{
    #[Test]
    public function canGetTitleWhenContentTypeIsFormUrlEncoded(): void
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
            ->willReturn(GetAllInputDTO::class);

        /** @var $iterable Generator */
        $iterable = new GetAllInputDTOResolver()->resolve($request, $argument);

        $dto = $iterable->current();

        $this->assertInstanceOf(GetAllInputDTO::class, $dto);
        $this->assertSame($title, $dto->title());
    }
}
