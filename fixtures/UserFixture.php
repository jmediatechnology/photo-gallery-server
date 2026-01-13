<?php

namespace App\Fixtures;

use App\Infrastructure\Symfony\Security\User\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixture extends Fixture
{
    private const array DATA = [
        ['username' => 'admin', 'password' => 'admin', 'roles' => ['ROLE_USER', 'ROLE_ADMIN']],
        ['username' => 'user1', 'password' => 'user1', 'roles' => ['ROLE_USER']],
        ['username' => 'user3', 'password' => 'user3', 'roles' => ['ROLE_USER']],
    ];

    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ){ }

    public function load(ObjectManager $manager): void
    {
        foreach (self::DATA as $data) {
            $username = (string) ($data['username'] ?? '');
            $password = (string) ($data['password'] ?? '');
            $roles = (array) ($data['roles'] ?? []);

            $passwordHashed = $this->passwordHasher->hashPassword(
                user: new User(
                    username: $username,
                    password: $password,
                    roles: $roles,
                ),
                plainPassword: $password
            );

            $user = new User(
                username: $username,
                password: $passwordHashed,
                roles: $roles,
            );

            $manager->persist($user);
        }

        $manager->flush();
    }
}
