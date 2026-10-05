<?php

declare(strict_types=1);

namespace App\Tests\Application\Photograph;

use App\Domain\Entity\Photograph;
use App\Domain\Enum\PhotographSortDirection;
use App\Domain\Enum\PhotographSortField;
use App\Domain\ValueObject\CreatedAt;
use App\Domain\ValueObject\Description;
use App\Domain\ValueObject\FilePath;
use App\Domain\ValueObject\Title;
use App\Domain\ValueObject\UpdatedAt;
use App\Domain\ValueObject\UUID;
use App\Tests\Application\ApiTestCase;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class GetAllActionTest extends ApiTestCase
{
    #[Test]
    public function canGetAllPhotographs(): void
    {
        $photograph1 = new Photograph(
            uuid: new UUID('0ab636b9-ba6d-4500-8f14-4f0b88536518'),
            title:  new Title('photograph 1'),
            description: new Description('Description for photograph 1'),
            filePath: new FilePath('public/images/0ab636b9-ba6d-4500-8f14-4f0b88536518.jpg'),
            createdAt:  new CreatedAt(new DateTimeImmutable('01-01-2026 08:00:00')),
            updatedAt: new UpdatedAt(new DateTimeImmutable('01-01-2026 08:00:00')),
        );

        $photograph2 = new Photograph(
            uuid: new UUID('19e826e6-c26b-4f2b-92fa-bfe07773f209'),
            title: new Title('photograph 2'),
            description: new Description('Description for photograph 2'),
            filePath: new FilePath('public/images/19e826e6-c26b-4f2b-92fa-bfe07773f209.jpg'),
            createdAt: new CreatedAt(new DateTimeImmutable('01-01-2026 08:10:00')),
            updatedAt: new UpdatedAt(new DateTimeImmutable('01-01-2026 08:10:00')),
        );

        $photograph3 = new Photograph(
            uuid: new UUID('2fef35d9-5dde-4abd-b247-a3214d7e6115'),
            title: new Title('photograph 3'),
            description: new Description('Description for photograph 3'),
            filePath: new FilePath('public/images/2fef35d9-5dde-4abd-b247-a3214d7e6115.jpg'),
            createdAt: new CreatedAt(new DateTimeImmutable('01-01-2026 08:20:00')),
            updatedAt: new UpdatedAt(new DateTimeImmutable('01-01-2026 08:20:00')),
        );

        $this->photographRepository->save($photograph1);
        $this->photographRepository->save($photograph2);
        $this->photographRepository->save($photograph3);

        $json = $this->jsonRequest(
            method: 'GET',
            uri: '/photographs',
        );

        self::assertCount(3, $json);

        [$photograph3, $photograph2, $photograph1] = $json;

        self::assertArrayHasKey('uuid', $photograph1);
        self::assertArrayHasKey('title', $photograph1);
        self::assertArrayHasKey('description', $photograph1);
        self::assertSame('0ab636b9-ba6d-4500-8f14-4f0b88536518', $photograph1['uuid']);
        self::assertSame('photograph 1', $photograph1['title']);
        self::assertSame('Description for photograph 1', $photograph1['description']);
        self::assertSame('2026-01-01 08:00:00', $photograph1['createdAt']);
        self::assertSame('2026-01-01 08:00:00', $photograph1['updatedAt']);

        self::assertArrayHasKey('uuid', $photograph2);
        self::assertArrayHasKey('title', $photograph2);
        self::assertArrayHasKey('description', $photograph2);
        self::assertSame('19e826e6-c26b-4f2b-92fa-bfe07773f209', $photograph2['uuid']);
        self::assertSame('photograph 2', $photograph2['title']);
        self::assertSame('Description for photograph 2', $photograph2['description']);
        self::assertSame('public/images/19e826e6-c26b-4f2b-92fa-bfe07773f209.jpg', $photograph2['filePath']);
        self::assertSame('2026-01-01 08:10:00', $photograph2['createdAt']);
        self::assertSame('2026-01-01 08:10:00', $photograph2['updatedAt']);

        self::assertArrayHasKey('uuid', $photograph3);
        self::assertArrayHasKey('title', $photograph3);
        self::assertArrayHasKey('description', $photograph3);
        self::assertSame('2fef35d9-5dde-4abd-b247-a3214d7e6115', $photograph3['uuid']);
        self::assertSame('photograph 3', $photograph3['title']);
        self::assertSame('Description for photograph 3', $photograph3['description']);
        self::assertSame('public/images/2fef35d9-5dde-4abd-b247-a3214d7e6115.jpg', $photograph3['filePath']);
        self::assertSame('2026-01-01 08:20:00', $photograph3['createdAt']);
        self::assertSame('2026-01-01 08:20:00', $photograph3['updatedAt']);

        self::assertResponseIsSuccessful();
    }

    #[Test]
    public function canGetAllPhotographsByExactTitle(): void
    {
        $photograph1 = new Photograph(
            uuid: new UUID('0fec74d1-fa33-42af-b01b-da43f868a659'),
            title:  new Title('photograph 1'),
            description: new Description('Description for photograph 1'),
            filePath: new FilePath('public/images/0fec74d1-fa33-42af-b01b-da43f868a659.jpg'),
            createdAt:  new CreatedAt(new DateTimeImmutable('01-01-2026 08:00:00')),
            updatedAt: new UpdatedAt(new DateTimeImmutable('01-01-2026 08:00:00')),
        );

        $photograph2 = new Photograph(
            uuid: new UUID('2d6d1e16-7a2e-4139-a66a-5b18f290c057'),
            title: new Title('photograph 2'),
            description: new Description('Description for photograph 2'),
            filePath: new FilePath('public/images/2d6d1e16-7a2e-4139-a66a-5b18f290c057.jpg'),
            createdAt: new CreatedAt(new DateTimeImmutable('01-01-2026 08:10:00')),
            updatedAt: new UpdatedAt(new DateTimeImmutable('01-01-2026 08:10:00')),
        );

        $photograph3 = new Photograph(
            uuid: new UUID('af8520b8-956e-4da1-b3d6-400c6e69e6a5'),
            title: new Title('photograph 3'),
            description: new Description('Description for photograph 3'),
            filePath: new FilePath('public/images/af8520b8-956e-4da1-b3d6-400c6e69e6a5.jpg'),
            createdAt: new CreatedAt(new DateTimeImmutable('01-01-2026 08:20:00')),
            updatedAt: new UpdatedAt(new DateTimeImmutable('01-01-2026 08:20:00')),
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
        self::assertSame('2026-01-01 08:00:00', $photograph1['createdAt']);
        self::assertSame('2026-01-01 08:00:00', $photograph1['updatedAt']);

        self::assertResponseIsSuccessful();
    }

    #[Test]
    public function canGetAllPhotographsByPartiallyContainingTitle(): void
    {
        $photograph1 = new Photograph(
            uuid: new UUID('11b47022-b1bb-447e-8956-4e60bcd34d7c'),
            title:  new Title('My awesome title number 1'),
            description: new Description('Description for photograph 1'),
            filePath: new FilePath('public/images/11b47022-b1bb-447e-8956-4e60bcd34d7c.jpg'),
            createdAt:  new CreatedAt(new DateTimeImmutable('01-01-2026 08:00:00')),
            updatedAt: new UpdatedAt(new DateTimeImmutable('01-01-2026 08:00:00')),
        );

        $photograph2 = new Photograph(
            uuid: new UUID('2510a8b6-5749-4f3f-8e2b-7183fb8024b8'),
            title: new Title('My awesome title number 2'),
            description: new Description('Description for photograph 2'),
            filePath: new FilePath('public/images/2510a8b6-5749-4f3f-8e2b-7183fb8024b8.jpg'),
            createdAt: new CreatedAt(new DateTimeImmutable('01-01-2026 08:10:00')),
            updatedAt: new UpdatedAt(new DateTimeImmutable('01-01-2026 08:10:00')),
        );

        $photograph3 = new Photograph(
            uuid: new UUID('31454994-6718-4813-9889-1e40e0d4d262'),
            title: new Title('My title number 3'),
            description: new Description('Description for photograph 3'),
            filePath: new FilePath('public/images/31454994-6718-4813-9889-1e40e0d4d262.jpg'),
            createdAt: new CreatedAt(new DateTimeImmutable('01-01-2026 08:20:00')),
            updatedAt: new UpdatedAt(new DateTimeImmutable('01-01-2026 08:20:00')),
        );

        $this->photographRepository->save($photograph1);
        $this->photographRepository->save($photograph2);
        $this->photographRepository->save($photograph3);

        $json = $this->jsonRequest(
            method: 'GET',
            uri: '/photographs',
            parameters: [
                'title' => 'awesome',
            ]
        );

        self::assertCount(2, $json);

        [$photograph2, $photograph1] = $json;

        self::assertArrayHasKey('uuid', $photograph1);
        self::assertArrayHasKey('title', $photograph1);
        self::assertArrayHasKey('description', $photograph1);
        self::assertSame('11b47022-b1bb-447e-8956-4e60bcd34d7c', $photograph1['uuid']);
        self::assertSame('My awesome title number 1', $photograph1['title']);
        self::assertSame('Description for photograph 1', $photograph1['description']);
        self::assertSame('public/images/11b47022-b1bb-447e-8956-4e60bcd34d7c.jpg', $photograph1['filePath']);
        self::assertSame('2026-01-01 08:00:00', $photograph1['createdAt']);
        self::assertSame('2026-01-01 08:00:00', $photograph1['updatedAt']);

        self::assertArrayHasKey('uuid', $photograph2);
        self::assertArrayHasKey('title', $photograph2);
        self::assertArrayHasKey('description', $photograph2);
        self::assertSame('2510a8b6-5749-4f3f-8e2b-7183fb8024b8', $photograph2['uuid']);
        self::assertSame('My awesome title number 2', $photograph2['title']);
        self::assertSame('Description for photograph 2', $photograph2['description']);
        self::assertSame('public/images/2510a8b6-5749-4f3f-8e2b-7183fb8024b8.jpg', $photograph2['filePath']);
        self::assertSame('2026-01-01 08:10:00', $photograph2['createdAt']);
        self::assertSame('2026-01-01 08:10:00', $photograph2['updatedAt']);

        self::assertResponseIsSuccessful();
    }


    #[Test]
    public function canGetAllPhotographsByEmptyTitle(): void
    {
        $photograph1 = new Photograph(
            uuid: new UUID('af3164aa-e97d-419c-b5ed-30c06f54d80e'),
            title:  new Title('My awesome title number 1'),
            description: new Description('Description for photograph 1'),
            filePath: new FilePath('public/images/af3164aa-e97d-419c-b5ed-30c06f54d80e.jpg'),
            createdAt:  new CreatedAt(new DateTimeImmutable('01-01-2026 08:00:00')),
            updatedAt: new UpdatedAt(new DateTimeImmutable('01-01-2026 08:00:00')),
        );

        $photograph2 = new Photograph(
            uuid: new UUID('bcaf4b21-934b-41d9-b366-af7ab465c5ef'),
            title: new Title('My awesome title number 2'),
            description: new Description('Description for photograph 2'),
            filePath: new FilePath('public/images/bcaf4b21-934b-41d9-b366-af7ab465c5ef.jpg'),
            createdAt: new CreatedAt(new DateTimeImmutable('01-01-2026 08:10:00')),
            updatedAt: new UpdatedAt(new DateTimeImmutable('01-01-2026 08:10:00')),
        );

        $photograph3 = new Photograph(
            uuid: new UUID('c59c6eaf-1051-4f19-8548-eaff623095a6'),
            title: new Title('My awesome title number 3'),
            description: new Description('Description for photograph 3'),
            filePath: new FilePath('public/images/c59c6eaf-1051-4f19-8548-eaff623095a6.jpg'),
            createdAt: new CreatedAt(new DateTimeImmutable('01-01-2026 08:20:00')),
            updatedAt: new UpdatedAt(new DateTimeImmutable('01-01-2026 08:20:00')),
        );

        $this->photographRepository->save($photograph1);
        $this->photographRepository->save($photograph2);
        $this->photographRepository->save($photograph3);

        $json = $this->jsonRequest(
            method: 'GET',
            uri: '/photographs',
            parameters: [
                'title' => '',
            ]
        );

        self::assertCount(3, $json);

        [$photograph3, $photograph2, $photograph1] = $json;

        self::assertArrayHasKey('uuid', $photograph1);
        self::assertArrayHasKey('title', $photograph1);
        self::assertArrayHasKey('description', $photograph1);
        self::assertSame('af3164aa-e97d-419c-b5ed-30c06f54d80e', $photograph1['uuid']);
        self::assertSame('My awesome title number 1', $photograph1['title']);
        self::assertSame('Description for photograph 1', $photograph1['description']);
        self::assertSame('public/images/af3164aa-e97d-419c-b5ed-30c06f54d80e.jpg', $photograph1['filePath']);
        self::assertSame('2026-01-01 08:00:00', $photograph1['createdAt']);
        self::assertSame('2026-01-01 08:00:00', $photograph1['updatedAt']);

        self::assertArrayHasKey('uuid', $photograph2);
        self::assertArrayHasKey('title', $photograph2);
        self::assertArrayHasKey('description', $photograph2);
        self::assertSame('bcaf4b21-934b-41d9-b366-af7ab465c5ef', $photograph2['uuid']);
        self::assertSame('My awesome title number 2', $photograph2['title']);
        self::assertSame('Description for photograph 2', $photograph2['description']);
        self::assertSame('public/images/bcaf4b21-934b-41d9-b366-af7ab465c5ef.jpg', $photograph2['filePath']);
        self::assertSame('2026-01-01 08:10:00', $photograph2['createdAt']);
        self::assertSame('2026-01-01 08:10:00', $photograph2['updatedAt']);

        self::assertArrayHasKey('uuid', $photograph3);
        self::assertArrayHasKey('title', $photograph3);
        self::assertArrayHasKey('description', $photograph3);
        self::assertSame('c59c6eaf-1051-4f19-8548-eaff623095a6', $photograph3['uuid']);
        self::assertSame('My awesome title number 3', $photograph3['title']);
        self::assertSame('Description for photograph 3', $photograph3['description']);
        self::assertSame('public/images/c59c6eaf-1051-4f19-8548-eaff623095a6.jpg', $photograph3['filePath']);
        self::assertSame('2026-01-01 08:20:00', $photograph3['createdAt']);
        self::assertSame('2026-01-01 08:20:00', $photograph3['updatedAt']);

        self::assertResponseIsSuccessful();
    }

    #[Test]
    #[DataProvider('provideSortFieldSortDirection')]
    public function canGetAllPhotographsSortedBy(PhotographSortField $sortField, PhotographSortDirection $sortDirection): void
    {
        $photographA = new Photograph(
            uuid: new UUID('af3164aa-e97d-419c-b5ed-30c06f54d80e'),
            title:  new Title('AAA'),
            description: new Description('Description for photograph 1'),
            filePath: new FilePath('public/images/af3164aa-e97d-419c-b5ed-30c06f54d80e.jpg'),
            createdAt:  new CreatedAt(new DateTimeImmutable('01-01-2026 08:00:00')),
            updatedAt: new UpdatedAt(new DateTimeImmutable('01-01-2026 08:00:00')),
        );

        $photographB = new Photograph(
            uuid: new UUID('bcaf4b21-934b-41d9-b366-af7ab465c5ef'),
            title: new Title('BBB'),
            description: new Description('Description for photograph 2'),
            filePath: new FilePath('public/images/bcaf4b21-934b-41d9-b366-af7ab465c5ef.jpg'),
            createdAt: new CreatedAt(new DateTimeImmutable('01-01-2026 08:10:00')),
            updatedAt: new UpdatedAt(new DateTimeImmutable('01-01-2026 08:10:00')),
        );

        $photographC = new Photograph(
            uuid: new UUID('c59c6eaf-1051-4f19-8548-eaff623095a6'),
            title: new Title('CCC'),
            description: new Description('Description for photograph 3'),
            filePath: new FilePath('public/images/c59c6eaf-1051-4f19-8548-eaff623095a6.jpg'),
            createdAt: new CreatedAt(new DateTimeImmutable('01-01-2026 08:20:00')),
            updatedAt: new UpdatedAt(new DateTimeImmutable('01-01-2026 08:20:00')),
        );

        $this->photographRepository->save($photographA);
        $this->photographRepository->save($photographB);
        $this->photographRepository->save($photographC);

        $json = $this->jsonRequest(
            method: 'GET',
            uri: '/photographs',
            parameters: [
                'sortField' => $sortField->value,
                'sortDirection' => $sortDirection->value,
            ]
        );

        self::assertCount(3, $json);

        if ($sortField === PhotographSortField::CreatedAt && $sortDirection === PhotographSortDirection::Asc) {
            [$photograph1, $photograph2, $photograph3] = $json;
        }
        if ($sortField === PhotographSortField::CreatedAt && $sortDirection === PhotographSortDirection::Desc) {
            [$photograph3, $photograph2, $photograph1] = $json;
        }

        if ($sortField === PhotographSortField::UpdatedAt && $sortDirection === PhotographSortDirection::Asc) {
            [$photograph1, $photograph2, $photograph3] = $json;
        }
        if ($sortField === PhotographSortField::UpdatedAt && $sortDirection === PhotographSortDirection::Desc) {
            [$photograph3, $photograph2, $photograph1] = $json;
        }

        if ($sortField === PhotographSortField::Title && $sortDirection === PhotographSortDirection::Asc) {
            [$photograph1, $photograph2, $photograph3] = $json;
        }
        if ($sortField === PhotographSortField::Title && $sortDirection === PhotographSortDirection::Desc) {
            [$photograph3, $photograph2, $photograph1] = $json;
        }

        self::assertArrayHasKey('uuid', $photograph1);
        self::assertArrayHasKey('title', $photograph1);
        self::assertArrayHasKey('description', $photograph1);
        self::assertSame('af3164aa-e97d-419c-b5ed-30c06f54d80e', $photograph1['uuid']);
        self::assertSame('AAA', $photograph1['title']);
        self::assertSame('Description for photograph 1', $photograph1['description']);
        self::assertSame('public/images/af3164aa-e97d-419c-b5ed-30c06f54d80e.jpg', $photograph1['filePath']);
        self::assertSame('2026-01-01 08:00:00', $photograph1['createdAt']);
        self::assertSame('2026-01-01 08:00:00', $photograph1['updatedAt']);

        self::assertArrayHasKey('uuid', $photograph2);
        self::assertArrayHasKey('title', $photograph2);
        self::assertArrayHasKey('description', $photograph2);
        self::assertSame('bcaf4b21-934b-41d9-b366-af7ab465c5ef', $photograph2['uuid']);
        self::assertSame('BBB', $photograph2['title']);
        self::assertSame('Description for photograph 2', $photograph2['description']);
        self::assertSame('public/images/bcaf4b21-934b-41d9-b366-af7ab465c5ef.jpg', $photograph2['filePath']);
        self::assertSame('2026-01-01 08:10:00', $photograph2['createdAt']);
        self::assertSame('2026-01-01 08:10:00', $photograph2['updatedAt']);

        self::assertArrayHasKey('uuid', $photograph3);
        self::assertArrayHasKey('title', $photograph3);
        self::assertArrayHasKey('description', $photograph3);
        self::assertSame('c59c6eaf-1051-4f19-8548-eaff623095a6', $photograph3['uuid']);
        self::assertSame('CCC', $photograph3['title']);
        self::assertSame('Description for photograph 3', $photograph3['description']);
        self::assertSame('public/images/c59c6eaf-1051-4f19-8548-eaff623095a6.jpg', $photograph3['filePath']);
        self::assertSame('2026-01-01 08:20:00', $photograph3['createdAt']);
        self::assertSame('2026-01-01 08:20:00', $photograph3['updatedAt']);

        self::assertResponseIsSuccessful();
    }

    /**
     * @return array<array<string, string>>
     */
    public static function provideSortFieldSortDirection(): array
    {
        return [
            [PhotographSortField::CreatedAt, PhotographSortDirection::Asc],
            [PhotographSortField::CreatedAt, PhotographSortDirection::Desc],
            [PhotographSortField::UpdatedAt, PhotographSortDirection::Asc],
            [PhotographSortField::UpdatedAt, PhotographSortDirection::Desc],
            [PhotographSortField::Title, PhotographSortDirection::Asc],
            [PhotographSortField::Title, PhotographSortDirection::Desc],
        ];
    }
}
