<?php

namespace App\Presentation\DTO\Photograph;

class OutputDTO
{
    public function __construct(
        public string $uuid,
        public string $title,
        public ?string $description,
        public string $filePath,
        public string $createdAt,
        public string $updatedAt,
    ) {
    }
}
