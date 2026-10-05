<?php

namespace App\Domain\Criteria;

use App\Domain\Enum\PhotographSortDirection;
use App\Domain\Enum\PhotographSortField;

readonly class PhotographListCriteria
{
    public function __construct(
        private ?string $title,
        private PhotographSortField $sortField,
        private PhotographSortDirection $sortDirection,
    ) {}

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
