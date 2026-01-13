<?php

namespace App\Tests\Unit\Presentation\Resolver\Photograph;

use App\Presentation\DTO\Photograph\GetAllInputDTO;
use App\Presentation\Resolver\Photograph\GetAllInputDTOResolver;
use Generator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class GetAllInputDTOResolverTest extends TestCase
{
    #[Test]
    public function canGetTitleWhenContentTypeIsFormUrlEncoded(): void
    {
        $title = 'awesome title';

        $inputBag = $this->createMock(InputBag::class);
        $inputBag
            ->expects($this->once())
            ->method('get')
            ->with('title')
            ->willReturn($title);

        $headerBag = $this->createMock(HeaderBag::class);
        $headerBag
            ->expects($this->once())
            ->method('get')
            ->with('Content-Type')
            ->willReturn('application/x-www-form-urlencoded');

        $request = $this->createStub(Request::class);
        $request->request = $inputBag;
        $request->headers = $headerBag;

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
