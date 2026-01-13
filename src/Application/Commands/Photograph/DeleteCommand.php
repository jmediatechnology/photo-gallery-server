<?php

namespace App\Application\Commands\Photograph;

use App\Domain\Entity\photograph;

class DeleteCommand
{
    public function __construct(
        private Photograph $photograph,
    ) {
    }

    public function photograph(): Photograph
    {
        return $this->photograph;
    }
}
