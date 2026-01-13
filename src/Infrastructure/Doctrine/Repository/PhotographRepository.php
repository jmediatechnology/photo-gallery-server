<?php

namespace App\Infrastructure\Doctrine\Repository;

use App\Domain\Entity\Photograph;
use App\Domain\ValueObject\UUID;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\UnitOfWork;
use Doctrine\Persistence\ManagerRegistry;
use LogicException;

class PhotographRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Photograph::class);
    }

    public function findOneByUUID(UUID $uuid): ?Photograph
    {
        $photograph = $this->findOneBy([
            'uuid' => $uuid,
        ]);
        return $photograph instanceof Photograph ? $photograph : null;
    }

    public function save(Photograph $entity): void
    {
        $entityManager = $this->getEntityManager();
        $unitOfWork = $entityManager->getUnitOfWork();
        $classMetadata = $entityManager->getClassMetadata(Photograph::class);

        $uuid = $entity->uuid();
        if ($this->isManaged($unitOfWork, $classMetadata, $uuid)) {
            $this->handleSaveForEntityIdentityCollision($entityManager, $entity, $uuid);
        } else {
            $entityManager->persist($entity);
        }

        $entityManager->flush();
    }

    public function remove(Photograph $entity): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->remove($entity);
        $entityManager->flush();
    }

    private function isManaged(UnitOfWork $unitOfWork, ClassMetadata $classMetadata, ?UUID $uuid): bool
    {
        $object = $unitOfWork->tryGetById(
            id: $uuid,
            rootClassName: $classMetadata->rootEntityName,
        );
        return $object instanceof Photograph;
    }

    private function handleSaveForEntityIdentityCollision(
        EntityManagerInterface $entityManager,
        Photograph $entity,
        ?UUID $uuid,
    ): void
    {
        $managedEntity = $entityManager->find(Photograph::class, $uuid);
        if (!$managedEntity instanceof Photograph) {
            throw new LogicException(
                sprintf('Found supposedly managed Photograph<%s> entity that is not managed', $uuid)
            );
        }
        $managedEntity->apply($entity);
    }
}
