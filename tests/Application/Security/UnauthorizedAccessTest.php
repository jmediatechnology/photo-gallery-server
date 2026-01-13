<?php

namespace App\Tests\Application\Security;

use App\Tests\Application\ApiTestCase;
use PHPUnit\Framework\Attributes\Test;

class UnauthorizedAccessTest extends ApiTestCase
{
    #[Test]
    public function accessGetsDeniedForSecuredEndpointWithoutToken(): void
    {
        $this->client->jsonRequest(
            method: 'GET',
            uri: '/photographs',
        );

        $content = $this->client->getResponse()->getContent();
        $json = json_decode($content, true);

        self::assertArrayHasKey('code', $json);
        self::assertArrayHasKey('message', $json);

        self::assertResponseStatusCodeSame(401);
        self::assertSame(401, $json['code']);
        self::assertSame('JWT Token not found', $json['message']);
    }
}
