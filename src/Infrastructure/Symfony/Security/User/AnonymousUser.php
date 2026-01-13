<?php

namespace App\Infrastructure\Symfony\Security\User;

use Symfony\Component\Security\Core\User\UserInterface;

class AnonymousUser implements UserInterface
{
    public function __construct(
        private string $identifier = 'anonymous'
    ) {}

    public function getUserIdentifier(): string
    {
        return $this->identifier;
    }

    public function getRoles(): array
    {
        return ['ROLE_ANONYMOUS'];
    }

    /**
     * @deprecated since Symfony 7.3, erase credentials using the "__serialize()" method instead
     */
    public function eraseCredentials(): void
    {
        // do nothing
    }
}
