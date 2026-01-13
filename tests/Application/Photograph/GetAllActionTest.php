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

class GetAllActionTest extends ApiTestCase
{
    #[Test]
    public function canGetAllPhotographs(): void
    {
        $photograph1 = new Photograph(
            uuid: new UUID('85a34cb7-744d-44f1-8ab1-1d57abb5514d'),
            title:  new Title('photograph 1'),
            description: new Description('Description for photograph 1'),
            filePath: new FilePath('public/images/85a34cb7-744d-44f1-8ab1-1d57abb5514d.jpg'),
            createdAt:  new CreatedAt(new DateTimeImmutable()),
            updatedAt: new UpdatedAt(new DateTimeImmutable()),
        );

        $photograph2 = new Photograph(
            uuid: new UUID('e78de945-8cc1-4221-8410-c061b616df28'),
            title: new Title('photograph 2'),
            description: new Description('Description for photograph 2'),
            filePath: new FilePath('public/images/e78de945-8cc1-4221-8410-c061b616df28.jpg'),
            createdAt: new CreatedAt(new DateTimeImmutable()),
            updatedAt: new UpdatedAt(new DateTimeImmutable()),
        );

        $photograph3 = new Photograph(
            uuid: new UUID('af8520b8-956e-4da1-b3d6-400c6e69e6a5'),
            title: new Title('photograph 3'),
            description: new Description('Description for photograph 3'),
            filePath: new FilePath('public/images/af8520b8-956e-4da1-b3d6-400c6e69e6a5.jpg'),
            createdAt: new CreatedAt(new DateTimeImmutable()),
            updatedAt: new UpdatedAt(new DateTimeImmutable()),
        );

        $this->photographRepository->save($photograph1);
        $this->photographRepository->save($photograph2);
        $this->photographRepository->save($photograph3);

        $json = $this->jsonRequest(
            method: 'GET',
            uri: '/photographs',
        );

        self::assertCount(3, $json);

        [$photograph1, $photograph2, $photograph3] = $json;

        self::assertArrayHasKey('uuid', $photograph1);
        self::assertArrayHasKey('title', $photograph1);
        self::assertArrayHasKey('description', $photograph1);
        self::assertSame('85a34cb7-744d-44f1-8ab1-1d57abb5514d', $photograph1['uuid']);
        self::assertSame('photograph 1', $photograph1['title']);
        self::assertSame('Description for photograph 1', $photograph1['description']);
        self::assertSame('public/images/85a34cb7-744d-44f1-8ab1-1d57abb5514d.jpg', $photograph1['filePath']);

        self::assertArrayHasKey('uuid', $photograph2);
        self::assertArrayHasKey('title', $photograph2);
        self::assertArrayHasKey('description', $photograph2);
        self::assertSame('e78de945-8cc1-4221-8410-c061b616df28', $photograph2['uuid']);
        self::assertSame('photograph 2', $photograph2['title']);
        self::assertSame('Description for photograph 2', $photograph2['description']);
        self::assertSame('public/images/e78de945-8cc1-4221-8410-c061b616df28.jpg', $photograph2['filePath']);

        self::assertArrayHasKey('uuid', $photograph3);
        self::assertArrayHasKey('title', $photograph3);
        self::assertArrayHasKey('description', $photograph3);
        self::assertSame('af8520b8-956e-4da1-b3d6-400c6e69e6a5', $photograph3['uuid']);
        self::assertSame('photograph 3', $photograph3['title']);
        self::assertSame('Description for photograph 3', $photograph3['description']);
        self::assertSame('public/images/af8520b8-956e-4da1-b3d6-400c6e69e6a5.jpg', $photograph3['filePath']);

        self::assertResponseIsSuccessful();
    }

    #[Test]
    public function canGetAllPhotographsByTitle(): void
    {
        $photograph1 = new Photograph(
            uuid: new UUID('0fec74d1-fa33-42af-b01b-da43f868a659'),
            title:  new Title('photograph 1'),
            description: new Description('Description for photograph 1'),
            filePath: new FilePath('public/images/0fec74d1-fa33-42af-b01b-da43f868a659.jpg'),
            createdAt:  new CreatedAt(new DateTimeImmutable()),
            updatedAt: new UpdatedAt(new DateTimeImmutable()),
        );

        $photograph2 = new Photograph(
            uuid: new UUID('2d6d1e16-7a2e-4139-a66a-5b18f290c057'),
            title: new Title('photograph 2'),
            description: new Description('Description for photograph 2'),
            filePath: new FilePath('public/images/2d6d1e16-7a2e-4139-a66a-5b18f290c057.jpg'),
            createdAt: new CreatedAt(new DateTimeImmutable()),
            updatedAt: new UpdatedAt(new DateTimeImmutable()),
        );

        $photograph3 = new Photograph(
            uuid: new UUID('af8520b8-956e-4da1-b3d6-400c6e69e6a5'),
            title: new Title('photograph 3'),
            description: new Description('Description for photograph 3'),
            filePath: new FilePath('public/images/af8520b8-956e-4da1-b3d6-400c6e69e6a5.jpg'),
            createdAt: new CreatedAt(new DateTimeImmutable()),
            updatedAt: new UpdatedAt(new DateTimeImmutable()),
        );

        $this->photographRepository->save($photograph1);
        $this->photographRepository->save($photograph2);
        $this->photographRepository->save($photograph3);

        $json = $this->jsonRequest(
            method: 'GET',
            uri: '/photographs',
            parameters: [
                'title' => 'photograph 1',
            ]
        );

        self::assertCount(1, $json);

        [$photograph1] = $json;

        self::assertArrayHasKey('uuid', $photograph1);
        self::assertArrayHasKey('title', $photograph1);
        self::assertArrayHasKey('description', $photograph1);
        self::assertSame('0fec74d1-fa33-42af-b01b-da43f868a659', $photograph1['uuid']);
        self::assertSame('photograph 1', $photograph1['title']);
        self::assertSame('Description for photograph 1', $photograph1['description']);
        self::assertSame('public/images/0fec74d1-fa33-42af-b01b-da43f868a659.jpg', $photograph1['filePath']);

        self::assertResponseIsSuccessful();
    }
}
