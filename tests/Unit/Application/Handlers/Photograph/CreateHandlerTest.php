<?php

namespace App\Tests\Unit\Application\Handlers\Photograph;

use App\Application\Commands\Photograph\CreateCommand;
use App\Application\Factory\FilePath\Factory as FilePathFactory;
use App\Application\Factory\Photograph\Factory;
use App\Application\Handler\Photograph\CreateHandler;
use App\Application\Upload\FileUploader;
use App\Domain\Entity\Photograph;
use App\Infrastructure\Doctrine\Repository\PhotographRepository;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CreateHandlerTest extends TestCase
{
    #[Test]
    public function canSave(): void
    {
        $photograph = $this->createStub(Photograph::class);
        $command = $this->createStub(CreateCommand::class);
        $fileUploader = $this->createStub(FileUploader::class);

        $factory = $this->createMock(Factory::class);
        $factory
            ->expects($this->once())
            ->method('createByCreateCommand')
            ->with($command)
            ->willReturn($photograph);

        $photographRepository = $this->createMock(PhotographRepository::class);
        $photographRepository
            ->expects($this->once())
            ->method('save')
            ->with($photograph);

        $filePathFactory = $this->createStub(FilePathFactory::class);

        $actual = new CreateHandler(
            $photographRepository,
            $factory,
            $fileUploader,
            $filePathFactory,
        )->__invoke($command);

        $this->assertInstanceOf(Photograph::class, $photograph);
        $this->assertSame($actual, $photograph);
    }
}
