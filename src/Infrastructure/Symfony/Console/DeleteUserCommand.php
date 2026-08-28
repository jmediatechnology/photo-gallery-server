<?php

namespace App\Infrastructure\Symfony\Console;

use App\Infrastructure\Doctrine\Repository\UserRepository;
use App\Infrastructure\Symfony\Security\User\User;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Ask;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Validator\Constraints\NotBlank;

#[AsCommand(name: 'app:user:delete', description: 'Delete a user')]
readonly class DeleteUserCommand
{
    public function __construct(
        private UserRepository $userRepository,
    ) {
    }

    public function __invoke(
        SymfonyStyle $io,
        #[Argument]
        #[Ask('Username', constraints: [new NotBlank()])]
        string $username,
    ): int {

        $user = $this->userRepository->findOneBy(['username' => $username]);
        if (!$user instanceof User) {
            $io->warning(sprintf('User "%s" was not found.', $username));
            return Command::INVALID;
        }

        $this->userRepository->remove($user);

        $user = $this->userRepository->findOneBy(['username' => $username]);
        if ($user instanceof User) {
            $io->warning(sprintf('User "%s" was not deleted.', $username));
            return Command::INVALID;
        }

        $io->success(sprintf('User "%s" was deleted.', $username));

        return Command::SUCCESS;
    }
}
