<?php

namespace App\Application\Factory\Photograph;

use App\Application\Commands\Photograph\CreateCommand;
use App\Domain\Entity\Photograph;
use App\Domain\ValueObject\CreatedAt;
use App\Domain\ValueObject\Description;
use App\Domain\ValueObject\FilePath;
use App\Domain\ValueObject\Title;
use App\Domain\ValueObject\UpdatedAt;
use App\Domain\ValueObject\UUID;
use DateTimeImmutable;

class Factory
{
    public function createByCreateCommand(CreateCommand $command, FilePath $filePath): Photograph
    {
        $uuid = $command->uuid() !== null ? new UUID($command->uuid()) : null;
        $title = new Title($command->title());
        $description = $command->description() !== null ? new Description($command->description()) : null;

        return new Photograph(
            uuid: $uuid,
            title: $title,
            description: $description,
            filePath: $filePath,
            createdAt: new CreatedAt(new DateTimeImmutable()),
            updatedAt: new UpdatedAt(new DateTimeImmutable()),
        );
    }
}
