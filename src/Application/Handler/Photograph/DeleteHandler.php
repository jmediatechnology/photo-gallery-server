<?php

namespace App\Application\Handler\Photograph;

use App\Application\Commands\Photograph\DeleteCommand;
use App\Domain\Entity\Photograph;
use App\Domain\Repository\PhotographRepositoryInterface;

class DeleteHandler
{
    public function __construct(private PhotographRepositoryInterface $photographRepository) {}

    public function __invoke(DeleteCommand $command): Photograph
    {
        $photograph = $command->photograph();
        $this->photographRepository->remove($photograph);
        return $photograph;
    }
}
