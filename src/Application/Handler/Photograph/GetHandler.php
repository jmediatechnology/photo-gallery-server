<?php

namespace App\Application\Handler\Photograph;

use App\Application\Query\Photograph\GetQuery;
use App\Domain\Entity\Photograph;
use App\Domain\Repository\Criteria\PhotographCriteria;
use App\Domain\Repository\PhotographRepositoryInterface;

class GetHandler
{
    public function __construct(private PhotographRepositoryInterface $photographRepository) {}

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
