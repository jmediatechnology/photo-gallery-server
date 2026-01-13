<?php

namespace App\Presentation\Action\Security;

use App\Infrastructure\Symfony\Security\User\AnonymousUser;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class AnonymousLoginAction
{
    public function __construct(private JWTTokenManagerInterface $jwtManager) {}

    public function __invoke(): JsonResponse
    {
        $user = new AnonymousUser('anonymous');
        $token = $this->jwtManager->create($user);

        return new JsonResponse(['token' => $token]);
    }
}
