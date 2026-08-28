<?php

namespace App\Tests\Unit\Infrastructure\Symfony\Console;

use App\Infrastructure\Symfony\Console\CreateUserCommand;
use App\Infrastructure\Symfony\Security\User\User;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class CreateUserCommandTest extends TestCase
{
    #[Test]
    public function canBeExecuted(): void
    {
        $username = 'username';
        $password = 'password';
        $hashedPassword = 'hashedPassword';

        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $passwordHasher
            ->expects($this->once())
            ->method('hashPassword')
            ->with(
                $this->isInstanceOf(User::class),
                $password,
            )
            ->willReturn($hashedPassword);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->expects($this->once())
            ->method('persist')
            ->with(
                $this->isInstanceOf(User::class),
            );

        $entityManager
            ->expects($this->once())
            ->method('flush');

        $io = $this->createMock(SymfonyStyle::class);
        $io
            ->expects($this->once())
            ->method('success')
            ->with(sprintf('User "%s" created.', $username));

        $command = new CreateUserCommand(
            passwordHasher: $passwordHasher,
            entityManager: $entityManager
        );
        $exitCode = $command->__invoke(
            io: $io,
            username: 'username',
            password: 'password',
            roles: ['ROLE_ADMIN']
        );

        $this->assertEquals(Command::SUCCESS, $exitCode);
    }
}
