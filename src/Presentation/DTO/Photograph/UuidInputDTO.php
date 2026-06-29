<?php

namespace App\Presentation\DTO\Photograph;

class UuidInputDTO
{
    public function __construct(
        private string $uuid
    ) {
    }

    public function uuid(): string
    {
        return $this->uuid;
    }
}
