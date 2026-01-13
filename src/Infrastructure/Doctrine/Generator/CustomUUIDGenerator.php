<?php

namespace App\Infrastructure\Doctrine\Generator;

use App\Domain\Entity\photograph;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Id\AbstractIdGenerator;
use Symfony\Component\Uid\Uuid;
use App\Domain\ValueObject\UUID as CustomUUID;

class CustomUUIDGenerator extends AbstractIdGenerator
{
    public function generateId(EntityManagerInterface $em, ?object $entity): mixed
    {
        if ($entity instanceof photograph) {
            $uuid = $entity->uuid();
            if ($uuid !== null) {
                return $uuid;
            }
        }

        $symfonyUUID = Uuid::v4()->toString();
        return new CustomUUID($symfonyUUID);
    }
}
