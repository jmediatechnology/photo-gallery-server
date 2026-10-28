<?php

namespace App\Infrastructure\Doctrine\Repository;

use App\Domain\Entity\Photograph;
use App\Domain\Enum\PhotographSortField;
use App\Domain\Repository\Criteria\PhotographCriteria;
use App\Domain\Repository\PhotographRepositoryInterface;
use App\Domain\ValueObject\Title;
use App\Domain\ValueObject\UUID;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\UnitOfWork;
use Doctrine\Persistence\ManagerRegistry;
use LogicException;

class PhotographRepository extends ServiceEntityRepository implements PhotographRepositoryInterface
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

    public function findOneByTitle(Title $title): ?Photograph
    {
        $photograph = $this->findOneBy([
            'title' => $title,
        ]);
        return $photograph instanceof Photograph ? $photograph : null;
    }

    /**
     * @param array<UUID> $uuids
     * @return array<Photograph>
     */
    public function findByUUIDs(array $uuids): array
    {
        $uuidStrings = array_map(
            callback: static fn (UUID $uuid): string => $uuid->__toString(),
            array: $uuids,
        );

        return $this->createQueryBuilder('photograph')
            ->andWhere('photograph.uuid IN (:uuids)')
            ->setParameter('uuids', $uuidStrings, ArrayParameterType::STRING)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array<Photograph>
     */
    public function findMatching(PhotographCriteria $criteria): array
    {
        $queryBuilder = $this->createQueryBuilder('photograph');

        $title = $criteria->title();
        if ($title !== null && $title !== '') {
            $queryBuilder
                ->andWhere('LOWER(photograph.title) LIKE LOWER(:title) ESCAPE \'\\\'')
                ->setParameter('title', '%' . addcslashes($title, '%_\\') . '%');
        }

        $sortField = match ($criteria->sortField()) {
            PhotographSortField::CreatedAt => 'photograph.createdAt',
            PhotographSortField::UpdatedAt => 'photograph.updatedAt',
            PhotographSortField::Title => 'photograph.title',
        };
        $sortDirection = $criteria->sortDirection()->value;

        return $queryBuilder
            ->orderBy($sortField, $sortDirection)
            ->getQuery()
            ->getResult();
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
