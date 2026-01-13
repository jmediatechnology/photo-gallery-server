<?php

namespace App\Tests\Unit\Presentation\Resolver\Photograph;

use App\Presentation\DTO\Photograph\InputDTO;
use App\Presentation\Resolver\Photograph\InputDTOResolver;
use JsonException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\FileBag;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;

class InputDTOResolverTest extends TestCase
{
    /**
     * @throws JsonException
     */
    #[Test]
    public function returnsEmptyArrayWhenArgumentTypeIsNotInputDTO(): void
    {
        $request = $this->createStub(Request::class);
        $argument = $this->createMock(ArgumentMetadata::class);

        $argument
            ->expects($this->once())
            ->method('getType')
            ->willReturn('non-InputDTO');

        $resolved = new InputDTOResolver()->resolve($request, $argument);
        $actual = iterator_to_array($resolved);

        self::assertEmpty($actual);
    }

    #[Test]
    public function throwsUnsupportedMediaTypeHttpExceptionWhenContentTypeIsApplicationJson(): void
    {
        $this->expectException(UnsupportedMediaTypeHttpException::class);

        $fileBag = $this->createMock(FileBag::class);

        $fileBag
            ->expects($this->once())
            ->method('has')
            ->with(0)
            ->willReturn(true);

        $fileBag
            ->expects($this->once())
            ->method('get')
            ->with(0)
            ->willReturn($this->createStub(UploadedFile::class));

        $headerBag = $this->createMock(HeaderBag::class);
        $headerBag
            ->expects($this->once())
            ->method('get')
            ->with('Content-Type')
            ->willReturn('application/json');

        $request = $this->createStub(Request::class);
        $request->files = $fileBag;
        $request->headers = $headerBag;

        $argument = $this->createMock(ArgumentMetadata::class);
        $argument
            ->expects($this->once())
            ->method('getType')
            ->willReturn(InputDTO::class);

        $resolver = new InputDTOResolver();
        iterator_to_array($resolver->resolve($request, $argument));
    }
}
