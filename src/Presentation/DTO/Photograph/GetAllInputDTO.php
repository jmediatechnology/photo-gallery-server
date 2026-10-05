<?php

namespace App\Presentation\DTO\Photograph;

use App\Domain\Enum\PhotographSortDirection;
use App\Domain\Enum\PhotographSortField;

class GetAllInputDTO
{
    public function __construct(
        private ?string $title = null,
        private ?PhotographSortField $sortField = null,
        private ?PhotographSortDirection $sortDirection = null,
    ) {}

    public function title(): ?string
    {
        return $this->title;
    }

    public function sortField(): ?PhotographSortField
    {
        return $this->sortField;
    }

    public function sortDirection(): ?PhotographSortDirection
    {
        return $this->sortDirection;
    }
}
