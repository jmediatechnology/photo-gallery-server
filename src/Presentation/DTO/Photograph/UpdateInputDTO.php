<?php

namespace App\Presentation\DTO\Photograph;

class UpdateInputDTO
{
    public function __construct(
        private string $title,
        private ?string $description = null,
    ) {
    }

    public function title(): string
    {
        return $this->title;
    }

    public function description(): ?string
    {
        return $this->description;
    }
}
