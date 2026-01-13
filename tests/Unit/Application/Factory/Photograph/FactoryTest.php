<?php

namespace App\Tests\Unit\Application\Factory\Photograph;

use App\Application\Commands\Photograph\CreateCommand;
use App\Application\Factory\Photograph\Factory;
use App\Domain\ValueObject\FilePath;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class FactoryTest extends TestCase
{
    #[Test]
    public function canCreateByCreateCommand(): void
    {
        $uuid = 'fd79bbdc-c3bd-44e2-a143-0bb808a4812d';
        $title = 'Awesome Title';
        $description = 'Awesome Description';

        $command = $this->createMock(CreateCommand::class);
        $command
            ->expects($this->exactly(2))
            ->method('uuid')
            ->willReturn($uuid);

        $command
            ->expects($this->exactly(1))
            ->method('title')
            ->willReturn($title);

        $command
            ->expects($this->exactly(2))
            ->method('description')
            ->willReturn($description);

        $filePath = $this->createStub(FilePath::class);

        $photograph = new Factory()->createByCreateCommand($command, $filePath);

        $this->assertSame($uuid, $photograph->uuid()->__toString());
        $this->assertSame($title, $photograph->title()->__toString());
        $this->assertSame($description, $photograph->description()->__toString());
        $this->assertSame($filePath, $photograph->filePath());
    }
}
