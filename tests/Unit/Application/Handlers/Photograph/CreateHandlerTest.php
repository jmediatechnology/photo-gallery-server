<?php

namespace App\Tests\Unit\Application\Handlers\Photograph;

use App\Application\Commands\Photograph\CreateCommand;
use App\Application\Factory\Filename\Factory as FilenameFactory;
use App\Application\Factory\FilePath\Factory as FilePathFactory;
use App\Application\Factory\Photograph\Factory as PhotographFactory;
use App\Application\Handler\Photograph\CreateHandler;
use App\Application\Upload\FileUploader;
use App\Domain\Entity\Photograph;
use App\Domain\Repository\PhotographRepositoryInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CreateHandlerTest extends TestCase
{
    #[Test]
    public function canSave(): void
    {
        $filenameFactory = $this->createStub(FilenameFactory::class);

        $photograph = $this->createStub(Photograph::class);
        $command = $this->createStub(CreateCommand::class);
        $fileUploader = $this->createStub(FileUploader::class);

        $photographFactory = $this->createMock(PhotographFactory::class);
        $photographFactory
            ->expects($this->once())
            ->method('createByCreateCommand')
            ->with($command)
            ->willReturn($photograph);

        $photographRepository = $this->createMock(PhotographRepositoryInterface::class);
        $photographRepository
            ->expects($this->once())
            ->method('save')
            ->with($photograph);

        $filePathFactory = $this->createStub(FilePathFactory::class);

        $actual = new CreateHandler(
            $filenameFactory,
            $filePathFactory,
            $fileUploader,
            $photographFactory,
            $photographRepository,
        )->__invoke($command);

        $this->assertInstanceOf(Photograph::class, $photograph);
        $this->assertSame($actual, $photograph);
    }
}
