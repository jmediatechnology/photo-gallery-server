<?php

namespace App\Tests\Application\Security;

use App\Fixtures\UserFixture;
use App\Tests\Application\ApiTestCase;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use PHPUnit\Framework\Attributes\Test;

class ObtainTokenActionTest extends ApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $databaseToolCollection = self::getContainer()->get(DatabaseToolCollection::class);
        assert($databaseToolCollection instanceof DatabaseToolCollection);

        $databaseTool = $databaseToolCollection->get();
        assert($databaseTool instanceof AbstractDatabaseTool);

        $databaseTool->loadFixtures([
            UserFixture::class,
        ]);
    }

    #[Test]
    public function canGetTokenForAdminUser(): void
    {
        $this->client->jsonRequest(
            method: 'POST',
            uri: '/api/login_check',
            parameters: [
                'username' => 'admin',
                'password' => 'admin',
            ],
        );

        $content = $this->client->getResponse()->getContent();
        $json = json_decode($content, true);

        self::assertArrayHasKey('token', $json);
        self::assertStringStartsWith('ey', $json['token']);
    }
}
