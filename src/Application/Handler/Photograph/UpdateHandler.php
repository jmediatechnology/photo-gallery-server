<?php

namespace App\Application\Handler\Photograph;

use App\Application\Commands\Photograph\UpdateCommand;
use App\Domain\Entity\Photograph;
use App\Domain\Repository\PhotographRepositoryInterface;
use App\Domain\ValueObject\Description;
use App\Domain\ValueObject\Title;
use App\Domain\ValueObject\UpdatedAt;
use DateTimeImmutable;

class UpdateHandler
{
    public function __construct(private PhotographRepositoryInterface $photographRepository) {}

    public function __invoke(UpdateCommand $command): Photograph
    {
        $title = new Title($command->title());
        $description = $command->description() !== null ? new Description($command->description()) : null;

        $updatedPhotograph = $command->photograph()
            ->withTitle($title)
            ->withDescription($description)
            ->withUpdatedAt(new UpdatedAt(new DateTimeImmutable()))
        ;

        $this->photographRepository->save($updatedPhotograph);

        return $updatedPhotograph;
    }
}
