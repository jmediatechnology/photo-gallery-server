<?php

declare(strict_types=1);

namespace App\Tests\Application\Photograph;

use App\Domain\Entity\Photograph;
use App\Domain\ValueObject\CreatedAt;
use App\Domain\ValueObject\Description;
use App\Domain\ValueObject\FilePath;
use App\Domain\ValueObject\Title;
use App\Domain\ValueObject\UpdatedAt;
use App\Domain\ValueObject\UUID;
use App\Tests\Application\ApiTestCase;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;

class UpdateActionTest extends ApiTestCase
{
    #[Test]
    public function canUpdatePhotograph(): void
    {
        $photograph = new Photograph(
            uuid: new UUID('f4bd198a-beac-4a71-b814-a6197fc55a6d'),
            title: new Title('Old Title'),
            description: new Description('Old Description'),
            filePath: new FilePath('public/images/f4bd198a-beac-4a71-b814-a6197fc55a6d.jpg'),
            createdAt: new CreatedAt(new DateTimeImmutable()),
            updatedAt: new UpdatedAt(new DateTimeImmutable()),
        );

        $this->photographRepository->save($photograph);

        $json = $this->jsonRequest(
            method: 'PATCH',
            uri: '/photographs/f4bd198a-beac-4a71-b814-a6197fc55a6d',
            parameters: [
                'title' => 'New Title',
                'description' => 'New description',
            ]
        );

        self::assertArrayHasKey('uuid', $json);
        self::assertArrayHasKey('title', $json);
        self::assertArrayHasKey('description', $json);
        self::assertSame('f4bd198a-beac-4a71-b814-a6197fc55a6d', $json['uuid']);
        self::assertSame('New Title', $json['title']);
        self::assertSame('New description', $json['description']);
        self::assertSame('public/images/f4bd198a-beac-4a71-b814-a6197fc55a6d.jpg', $json['filePath']);

        self::assertResponseIsSuccessful();

        $photograph = $this->photographRepository->find('f4bd198a-beac-4a71-b814-a6197fc55a6d');
        self::assertInstanceOf(Photograph::class, $photograph);
        self::assertEquals('f4bd198a-beac-4a71-b814-a6197fc55a6d', $photograph->uuid()?->__toString());
        self::assertEquals('New Title', $photograph->title()->__toString());
        self::assertEquals('New description', $photograph->description()?->__toString());
        self::assertEquals('public/images/f4bd198a-beac-4a71-b814-a6197fc55a6d.jpg', $photograph->filePath()->__toString());
    }

    #[Test]
    public function doesNotAllowUpdateWhenTitleIsNotUnique(): void
    {
        $photographA = new Photograph(
            uuid: new UUID('1d6d6fb0-af82-49a3-a696-8e5137e7f07e'),
            title: new Title('Title A'),
            description: new Description('Description A'),
            filePath: new FilePath('public/images/1d6d6fb0-af82-49a3-a696-8e5137e7f07e.jpg'),
            createdAt: new CreatedAt(new DateTimeImmutable()),
            updatedAt: new UpdatedAt(new DateTimeImmutable()),
        );

        $photographB = new Photograph(
            uuid: new UUID('22f42911-bc23-407f-ab42-1f87db511cc8'),
            title: new Title('Title B'),
            description: new Description('Description B'),
            filePath: new FilePath('public/images/22f42911-bc23-407f-ab42-1f87db511cc8.jpg'),
            createdAt: new CreatedAt(new DateTimeImmutable()),
            updatedAt: new UpdatedAt(new DateTimeImmutable()),
        );

        $this->photographRepository->save($photographA);
        $this->photographRepository->save($photographB);


        $json = $this->jsonRequest(
            method: 'PATCH',
            uri: '/photographs/22f42911-bc23-407f-ab42-1f87db511cc8',
            parameters: [
                'title' => 'Title A',
                'description' => 'New description',
            ]
        );

        self::assertResponseStatusCodeSame(422);
        self::assertArrayHasKey('errors', $json);
        self::assertStringContainsString('Title is already in use.', $json['errors']);
    }
}
