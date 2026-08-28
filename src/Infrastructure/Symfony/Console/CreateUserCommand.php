<?php

namespace App\Infrastructure\Symfony\Console;

use App\Infrastructure\Symfony\Security\User\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Ask;
use Symfony\Component\Console\Attribute\AskChoice;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

#[AsCommand(name: 'app:user:create', description: 'Create a user (intended for prod admin accounts)')]
readonly class CreateUserCommand
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        private EntityManagerInterface      $entityManager,
    ) {
    }

    public function __invoke(
        SymfonyStyle $io,
        #[Argument]
        #[Ask('Username', constraints: [new NotBlank()])]
        string $username,
        #[Argument]
        #[Ask('Password', hidden: true, constraints: [new NotBlank(), new Length(min: 6)])]
        string $password,
        #[Argument]
        #[AskChoice('Select roles', ['ROLE_USER', 'ROLE_ADMIN'])]
        array $roles,
    ): int {
        $user = new User(username: $username, password: '', roles: $roles);
        $hashed = $this->passwordHasher->hashPassword($user, $password);

        $user = new User(username: $username, password: $hashed, roles: $roles);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success(sprintf('User "%s" created.', $username));

        return Command::SUCCESS;
    }
}
