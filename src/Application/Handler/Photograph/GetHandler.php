<?php

namespace App\Application\Handler\Photograph;

use App\Application\Query\Photograph\GetQuery;
use App\Domain\Entity\Photograph;
use App\Infrastructure\Doctrine\Repository\PhotographRepository;

class GetHandler
{
    public function __construct(private PhotographRepository $photographRepository) {}

    /**
     * @return array<Photograph>
     */
    public function __invoke(GetQuery $query): array
    {
        $title = $query->title();

        if ($title === null) {
            return $this->photographRepository->findAll();
        }

        $photographs = $this->photographRepository->findBy([
            'title' => $title,
        ]);

        return $photographs;
    }
}
