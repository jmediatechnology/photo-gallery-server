<?php

namespace App\Domain\Repository;

use App\Domain\Entity\Photograph;
use App\Domain\Repository\Criteria\PhotographCriteria;
use App\Domain\ValueObject\Title;
use App\Domain\ValueObject\UUID;

interface PhotographRepositoryInterface
{
    public function findOneByUUID(UUID $uuid): ?Photograph;
    public function findOneByTitle(Title $title): ?Photograph;

    /** @param array<UUID> $uuids @return array<Photograph> */
    public function findByUUIDs(array $uuids): array;

    /** @return array<Photograph> */
    public function findMatching(PhotographCriteria $criteria): array;

    public function save(Photograph $entity): void;

    public function remove(Photograph $entity): void;
}
