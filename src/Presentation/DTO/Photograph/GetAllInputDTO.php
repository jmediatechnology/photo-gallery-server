<?php

namespace App\Presentation\DTO\Photograph;

class GetAllInputDTO
{
    public function __construct(
        private ?string $title = null,
    ) {
    }

    public function title(): ?string
    {
        return $this->title;
    }
}
