<?php

namespace App\Tests\Application\Security;

use App\Tests\Application\ApiTestCase;
use PHPUnit\Framework\Attributes\Test;

class AnonymousLoginActionTest extends ApiTestCase
{
    #[Test]
    public function canGetTokenForAnonymousUser(): void
    {
        $this->client->jsonRequest(
            method: 'GET',
            uri: '/api/login/anonymous',
        );

        $content = $this->client->getResponse()->getContent();
        $json = json_decode($content, true);

        self::assertArrayHasKey('token', $json);
        self::assertStringStartsWith('ey', $json['token']);
    }
}
