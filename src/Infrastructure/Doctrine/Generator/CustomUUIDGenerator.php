<?php

namespace App\Infrastructure\Doctrine\Generator;

use App\Domain\Entity\Photograph;
use App\Domain\ValueObject\UUID as CustomUUID;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Id\AbstractIdGenerator;
use Symfony\Component\Uid\Uuid;

class CustomUUIDGenerator extends AbstractIdGenerator
{
    public function generateId(EntityManagerInterface $em, ?object $entity): mixed
    {
        if ($entity instanceof Photograph) {
            $uuid = $entity->uuid();
            if ($uuid !== null) {
                return $uuid;
            }
        }

        $symfonyUUID = Uuid::v4()->toString();
        return new CustomUUID($symfonyUUID);
    }
}
