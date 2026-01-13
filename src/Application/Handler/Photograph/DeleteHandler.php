<?php

namespace App\Application\Handler\Photograph;

use App\Application\Commands\Photograph\DeleteCommand;
use App\Domain\Entity\Photograph;
use App\Infrastructure\Doctrine\Repository\PhotographRepository;

class DeleteHandler
{
    public function __construct(private PhotographRepository $photographRepository) {}

    public function __invoke(DeleteCommand $command): Photograph
    {
        $photograph = $command->photograph();
        $this->photographRepository->remove($photograph);
        return $photograph;
    }
}
