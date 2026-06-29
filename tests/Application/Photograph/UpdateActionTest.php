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
}
