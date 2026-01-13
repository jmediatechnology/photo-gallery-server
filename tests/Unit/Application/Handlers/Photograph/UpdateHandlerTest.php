<?php

namespace App\Tests\Unit\Application\Handlers\Photograph;

use App\Application\Commands\Photograph\UpdateCommand;
use App\Application\Handler\Photograph\UpdateHandler;
use App\Domain\Entity\Photograph;
use App\Infrastructure\Doctrine\Repository\PhotographRepository;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class UpdateHandlerTest extends TestCase
{
    #[Test]
    public function canSave(): void
    {
        $title = 'Awesome Title';
        $description = 'Awesome Description';

        $photograph = $this->createMock(Photograph::class);

        $command = $this->createMock(UpdateCommand::class);
        $command
            ->expects($this->once())
            ->method('photograph')
            ->willReturn($photograph);

        $command
            ->expects($this->once())
            ->method('title')
            ->willReturn($title);

        $command
            ->expects($this->exactly(2))
            ->method('description')
            ->willReturn($description);

        $photograph
            ->expects($this->once())
            ->method('withTitle')
            ->willReturnSelf();

        $photograph
            ->expects($this->once())
            ->method('withDescription')
            ->willReturnSelf();

        $photograph
            ->expects($this->once())
            ->method('withUpdatedAt')
            ->willReturnSelf();

        $photographRepository = $this->createMock(PhotographRepository::class);
        $photographRepository
            ->expects($this->once())
            ->method('save')
            ->with($photograph);

        $actual = new UpdateHandler($photographRepository)->__invoke($command);

        $this->assertInstanceOf(Photograph::class, $photograph);
        $this->assertSame($photograph, $actual);
    }
}
