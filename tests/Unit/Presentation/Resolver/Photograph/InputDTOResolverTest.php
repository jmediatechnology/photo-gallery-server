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

        $uploadedFile = $this->createStub(UploadedFile::class);

        $request = Request::create(uri: '/', method: 'POST');
        $request->initialize(
            files: [0 => $uploadedFile],
            server: ['CONTENT_TYPE' => 'application/json'],
        );

        $argument = $this->createMock(ArgumentMetadata::class);
        $argument
            ->expects($this->once())
            ->method('getType')
            ->willReturn(InputDTO::class);

        $resolver = new InputDTOResolver();
        iterator_to_array($resolver->resolve($request, $argument));
    }
}
