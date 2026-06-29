<?php

namespace App\Application\Query\Photograph;

class GenerateDescriptionForKnownPhotographUuidQuery
{
    public function __construct(
        public string $uuid,
    ) {
    }

    public function uuid(): string
    {
        return $this->uuid;
    }
}
