<?php

namespace App\Tests\Unit\Application\Handlers\Photograph;

use App\Application\Commands\Photograph\DeleteCommand;
use App\Application\Handler\Photograph\DeleteHandler;
use App\Domain\Entity\Photograph;
use App\Domain\Repository\PhotographRepositoryInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class DeleteHandlerTest extends TestCase
{
    #[Test]
    public function canRemove(): void
    {
        $photograph = $this->createStub(Photograph::class);

        $command = $this->createMock(DeleteCommand::class);
        $command
            ->expects($this->once())
            ->method('photograph')
            ->willReturn($photograph);

        $photographRepository = $this->createMock(PhotographRepositoryInterface::class);
        $photographRepository
            ->expects($this->once())
            ->method('remove')
            ->with($photograph);

        $actual = new DeleteHandler($photographRepository)->__invoke($command);

        $this->assertSame($photograph, $actual);
    }
}
