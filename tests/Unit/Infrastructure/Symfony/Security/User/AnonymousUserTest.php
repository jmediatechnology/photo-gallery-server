<?php

namespace App\Tests\Unit\Infrastructure\Symfony\Security\User;

use App\Infrastructure\Symfony\Security\User\AnonymousUser;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class AnonymousUserTest extends TestCase
{
    #[Test]
    public function eraseCredentialsDoesNothing(): void
    {
        new AnonymousUser()->eraseCredentials();

        $this->assertTrue(true);
    }
}
