<?php

namespace App\Application\Query\Photograph;

use App\Domain\Enum\PhotographSortDirection;
use App\Domain\Enum\PhotographSortField;

class GetQuery
{
    private PhotographSortField $sortField;
    private PhotographSortDirection $sortDirection;

    public function __construct(
        private ?string $title = null,
        ?PhotographSortField $sortField = null,
        ?PhotographSortDirection $sortDirection = null,
    ) {
        $this->sortField = $sortField ?? PhotographSortField::CreatedAt;
        $this->sortDirection = $sortDirection ?? PhotographSortDirection::Desc;
    }

    public function title(): ?string
    {
        return $this->title;
    }

    public function sortField(): PhotographSortField
    {
        return $this->sortField;
    }

    public function sortDirection(): PhotographSortDirection
    {
        return $this->sortDirection;
    }
}
