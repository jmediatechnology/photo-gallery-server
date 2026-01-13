<?php

namespace App\Application\Commands\Photograph;

use App\Domain\Entity\Photograph;

class UpdateCommand
{
    public function __construct(
        private Photograph $photograph,
        private string $title,
        private ?string $description = null,
    ) {
    }

    public function photograph(): Photograph
    {
        return $this->photograph;
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
