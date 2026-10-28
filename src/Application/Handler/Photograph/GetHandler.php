<?php

namespace App\Application\Handler\Photograph;

use App\Application\Query\Photograph\GetQuery;
use App\Domain\Entity\Photograph;
use App\Domain\Repository\Criteria\PhotographCriteria;
use App\Infrastructure\Doctrine\Repository\PhotographRepository;

class GetHandler
{
    public function __construct(private PhotographRepository $photographRepository) {}

    /**
     * @return array<Photograph>
     */
    public function __invoke(GetQuery $query): array
    {
        $criteria = new PhotographCriteria(
            title: $query->title(),
            sortField: $query->sortField(),
            sortDirection: $query->sortDirection(),
        );
        return $this->photographRepository->findMatching($criteria);
    }
}
