<?php

declare(strict_types=1);

namespace App\Tests\Integration\Infrastructure\Doctrine\Repository;

use App\Infrastructure\Doctrine\Repository\UserRepository;
use App\Infrastructure\Symfony\Security\User\User;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Platforms\SqlitePlatform;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class UserRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private UserRepository $userRepository;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        self::bootKernel();

        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->userRepository = static::getContainer()->get(UserRepository::class);

        if ($this->isUsingSqlite()) {
            $this->recreateSqliteSchema();
        }
    }

    #[Test]
    public function canSaveUser(): void
    {
        $user = new User('temporary_user', 'temporary_user', ['ROLE_USER']);
        $this->userRepository->save($user);

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => 'temporary_user']);

        self::assertNotNull($user);
    }

    #[Test]
    public function canRemoveUser(): void
    {
        $user = new User('temporary_user', 'temporary_user', ['ROLE_USER']);
        $this->userRepository->save($user);
        $this->userRepository->remove($user);

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => 'temporary_user']);

        self::assertNull($user);
    }


    /**
     * @throws Exception
     */
    private function isUsingSqlite(): bool
    {
        $connection = $this->entityManager->getConnection();
        $databasePlatform = $connection->getDatabasePlatform();
        return $databasePlatform instanceof SqlitePlatform;
    }

    private function recreateSqliteSchema(): void
    {
        $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();

        $schemaTool = new SchemaTool($this->entityManager);
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);
    }
}
