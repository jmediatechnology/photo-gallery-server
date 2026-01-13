<?php

declare(strict_types=1);

namespace App\Tests\Application\Photograph;

use App\Domain\Entity\photograph;
use App\Domain\ValueObject\CreatedAt;
use App\Domain\ValueObject\Description;
use App\Domain\ValueObject\FilePath;
use App\Domain\ValueObject\Title;
use App\Domain\ValueObject\UpdatedAt;
use App\Domain\ValueObject\UUID;
use App\Tests\Application\ApiTestCase;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;

class GetOneActionTest extends ApiTestCase
{
    #[Test]
    public function canGetOnePhotograph(): void
    {
        $photograph = new Photograph(
            uuid: new UUID('438144fb-3ea5-4bf8-b7cb-a4e271d6e6ee'),
            title: new Title('Title for canGetOnePhotograph'),
            description: new Description('Description for canGetOnePhotograph'),
            filePath: new FilePath('public/images/438144fb-3ea5-4bf8-b7cb-a4e271d6e6ee.jpg'),
            createdAt: new CreatedAt(new DateTimeImmutable()),
            updatedAt: new UpdatedAt(new DateTimeImmutable()),
        );

        $this->photographRepository->save($photograph);

        $json = $this->jsonRequest(
            method: 'GET',
            uri: '/photographs/438144fb-3ea5-4bf8-b7cb-a4e271d6e6ee',
        );

        self::assertArrayHasKey('uuid', $json);
        self::assertArrayHasKey('title', $json);
        self::assertArrayHasKey('description', $json);
        self::assertSame('438144fb-3ea5-4bf8-b7cb-a4e271d6e6ee', $json['uuid']);
        self::assertSame('Title for canGetOnePhotograph', $json['title']);
        self::assertSame('Description for canGetOnePhotograph', $json['description']);
        self::assertSame('public/images/438144fb-3ea5-4bf8-b7cb-a4e271d6e6ee.jpg', $json['filePath']);

        self::assertResponseIsSuccessful();
    }
}
