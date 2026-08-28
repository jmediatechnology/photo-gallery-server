<?php

namespace App\Tests\Unit\Infrastructure\Symfony\Console;

use App\Infrastructure\Doctrine\Repository\UserRepository;
use App\Infrastructure\Symfony\Console\DeleteUserCommand;
use App\Infrastructure\Symfony\Security\User\User;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

final class DeleteUserCommandTest extends TestCase
{
    #[Test]
    public function canDelete(): void
    {
        $username = 'username';

        $user = $this->createStub(User::class);

        $io = $this->createMock(SymfonyStyle::class);
        $io
            ->expects($this->once())
            ->method('success')
            ->with(sprintf('User "%s" was deleted.', $username));

        $callCount = 0;
        $userRepository = $this->createMock(UserRepository::class);
        $userRepository
            ->expects($this->exactly(2))
            ->method('findOneBy')
            ->with(['username' => $username])
            ->willReturnCallback(function () use (&$callCount, $user): ?User {
                $callCount++;
                return $callCount === 1 ? $user : null;
            });

        $userRepository
            ->expects($this->once())
            ->method('remove')
            ->with($user);

        $command = new DeleteUserCommand(
            userRepository: $userRepository,
        );
        $exitCode = $command->__invoke(
            io: $io,
            username: $username,
        );

        $this->assertEquals(Command::SUCCESS, $exitCode);
    }

    #[Test]
    public function showsWarningWhenAttemptingToDeleteUnknownUser(): void
    {
        $username = 'username';

        $user = $this->createStub(User::class);

        $io = $this->createMock(SymfonyStyle::class);
        $io
            ->expects($this->once())
            ->method('warning')
            ->with(sprintf('User "%s" was not found.', $username));

        $io
            ->expects($this->never())
            ->method('success');

        $userRepository = $this->createMock(UserRepository::class);
        $userRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['username' => $username])
            ->willReturn(null);

        $userRepository
            ->expects($this->never())
            ->method('remove')
            ->with($user);

        $command = new DeleteUserCommand(
            userRepository: $userRepository,
        );
        $exitCode = $command->__invoke(
            io: $io,
            username: $username,
        );

        $this->assertEquals(Command::INVALID, $exitCode);
    }


    #[Test]
    public function showsWarningWhenKnownUserWasNotDeleted(): void
    {
        $username = 'username';

        $user = $this->createStub(User::class);

        $io = $this->createMock(SymfonyStyle::class);
        $io
            ->expects($this->once())
            ->method('warning')
            ->with(sprintf('User "%s" was not deleted.', $username));

        $io
            ->expects($this->never())
            ->method('success');

        $userRepository = $this->createMock(UserRepository::class);
        $userRepository
            ->expects($this->exactly(2))
            ->method('findOneBy')
            ->with(['username' => $username])
            ->willReturn($user);

        $userRepository
            ->expects($this->once())
            ->method('remove')
            ->with($user);

        $command = new DeleteUserCommand(
            userRepository: $userRepository,
        );
        $exitCode = $command->__invoke(
            io: $io,
            username: $username,
        );

        $this->assertEquals(Command::INVALID, $exitCode);
    }
}
