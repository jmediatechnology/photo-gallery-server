<?php

namespace App\Tests\Unit\Infrastructure\Doctrine\Repository;

use App\Domain\Entity\Photograph;
use App\Domain\ValueObject\UUID;
use App\Infrastructure\Doctrine\Repository\PhotographRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\UnitOfWork;
use Doctrine\Persistence\ManagerRegistry;
use LogicException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PhotographRepositoryTest extends TestCase
{
    #[Test]
    public function throwsLogicExceptionWhenPhotographIsFoundInUnitOfWorkButNotThroughTheEntityManager(): void
    {
        $this->expectException(LogicException::class);

        $uuid = $this->createStub(UUID::class);

        $entity = $this->createMock(Photograph::class);
        $entity
            ->expects($this->once())
            ->method('uuid')
            ->willReturn($uuid);

        $unitOfWork = $this->createMock(UnitOfWork::class);
        $unitOfWork
            ->expects($this->once())
            ->method('tryGetById')
            ->willReturn($entity);

        $classMetadata = $this->createStub(ClassMetadata::class);
        $classMetadata->name = Photograph::class;
        $classMetadata->rootEntityName = Photograph::class;

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->expects($this->once())
            ->method('getUnitOfWork')
            ->willReturn($unitOfWork);

        $entityManager
            ->expects($this->atLeastOnce())
            ->method('getClassMetadata')
            ->willReturn($classMetadata);

        $entityManager
            ->expects($this->once())
            ->method('find')
            ->willReturn(null);

        $registry = $this->createMock(ManagerRegistry::class);
        $registry
            ->expects($this->once())
            ->method('getManagerForClass')
            ->willReturn($entityManager);

        new PhotographRepository($registry)->save($entity);
    }
}
