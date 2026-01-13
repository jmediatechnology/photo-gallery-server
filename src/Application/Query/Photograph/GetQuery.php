<?php

namespace App\Application\Query\Photograph;

class GetQuery
{
    public function __construct(
        public ?string $title = null,
    ) {
    }

    public function title(): ?string
    {
        return $this->title;
    }
}
