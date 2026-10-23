<?php

namespace App\Domain\Service;

use App\Domain\Entity\Photograph;
use App\Domain\ValueObject\Description;

interface ImageDescriptionGeneratorInterface
{
    public function describe(Photograph $photograph): Description;
}
