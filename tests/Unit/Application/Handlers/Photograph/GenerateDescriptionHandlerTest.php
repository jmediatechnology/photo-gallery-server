<?php

namespace App\Tests\Unit\Application\Handlers\Photograph;

use App\Application\Handler\Photograph\GenerateDescriptionHandler;
use App\Application\Query\Photograph\GenerateDescriptionForKnownPhotographUuidQuery;
use App\Domain\Entity\Photograph;
use App\Domain\ValueObject\FilePath;
use App\Infrastructure\Anthropic\Client\ImageDescriptionGeneratorInterface;
use App\Infrastructure\Doctrine\Repository\PhotographRepository;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use UnexpectedValueException;

final class GenerateDescriptionHandlerTest extends TestCase
{
    #[Test]
    public function triggersUnexpectedValueExceptionWhenPhotographRepositoryCantFindPhotograph(): void
    {
        $this->expectException(UnexpectedValueException::class);

        $photographRepository = $this->createMock(PhotographRepository::class);
        $photographRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->willReturn(null);

        $imageDescriptionGenerator = $this->createStub(ImageDescriptionGeneratorInterface::class);
        $publicImageDir = '';

        $query = $this->createStub(GenerateDescriptionForKnownPhotographUuidQuery::class);

        new GenerateDescriptionHandler(
            $photographRepository,
            $imageDescriptionGenerator,
            $publicImageDir,
        )->__invoke($query);
    }

    #[Test]
    public function triggersRuntimeExceptionWhenFileDoesNotExists(): void
    {
        $this->expectException(RuntimeException::class);

        $query = $this->createMock(GenerateDescriptionForKnownPhotographUuidQuery::class);
        $query
            ->expects($this->once())
            ->method('uuid')
            ->willReturn('3db32f49-4218-42eb-ae07-1a31916bac45');

        $filePath = $this->createMock(FilePath::class);
        $filePath
            ->expects($this->once())
            ->method('getBasename')
            ->willReturn('non-existing-basename.jpg');

        $photograph = $this->createMock(Photograph::class);
        $photograph
            ->expects($this->once())
            ->method('filePath')
            ->willReturn($filePath);

        $photographRepository = $this->createMock(PhotographRepository::class);
        $photographRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['uuid' => '3db32f49-4218-42eb-ae07-1a31916bac45'])
            ->willReturn($photograph);

        $imageDescriptionGenerator = $this->createStub(ImageDescriptionGeneratorInterface::class);
        $publicImageDir = '';

        new GenerateDescriptionHandler(
            $photographRepository,
            $imageDescriptionGenerator,
            $publicImageDir,
        )->__invoke($query);
    }
}
