<?php

namespace App\Tests\Unit\Domain\Entity;

use App\Domain\Entity\Photograph;
use App\Domain\ValueObject\CreatedAt;
use App\Domain\ValueObject\Description;
use App\Domain\ValueObject\FilePath;
use App\Domain\ValueObject\Title;
use App\Domain\ValueObject\UpdatedAt;
use App\Domain\ValueObject\UUID;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PhotographTest extends TestCase
{
    #[Test]
    public function canBeCreated(): void
    {
        $uuid = $this->createStub(UUID::class);
        $title = $this->createStub(Title::class);
        $description = $this->createStub(Description::class);
        $filePath = $this->createStub(FilePath::class);
        $createdAt = $this->createStub(CreatedAt::class);
        $updatedAt = $this->createStub(UpdatedAt::class);

        $photograph = new Photograph(
            uuid: $uuid,
            title: $title,
            description: $description,
            filePath: $filePath,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
        );

        $this->assertSame($uuid, $photograph->uuid());
        $this->assertSame($title, $photograph->title());
        $this->assertSame($description, $photograph->description());
        $this->assertSame($filePath, $photograph->filePath());
        $this->assertSame($createdAt, $photograph->createdAt());
        $this->assertSame($updatedAt, $photograph->updatedAt());
    }

    #[Test]
    public function canBeUpdated(): void
    {
        $uuid = $this->createStub(UUID::class);
        $title = $this->createStub(Title::class);
        $description = $this->createStub(Description::class);
        $filePath = $this->createStub(FilePath::class);
        $createdAt = $this->createStub(CreatedAt::class);
        $updatedAt = $this->createStub(UpdatedAt::class);

        $photograph = new Photograph(
            uuid: $uuid,
            title: $title,
            description: $description,
            filePath: $filePath,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
        );

        $updatedTitle = $this->createStub(Title::class);
        $updatedDescription = $this->createStub(Description::class);
        $updatedFilePath = $this->createStub(FilePath::class);
        $updatedCreatedAt = $this->createStub(CreatedAt::class);
        $updatedUpdatedAt = $this->createStub(UpdatedAt::class);

        $updatedPhotograph = $photograph
            ->withTitle($updatedTitle)
            ->withDescription($updatedDescription)
            ->withFilePath($updatedFilePath)
            ->withCreatedAt($updatedCreatedAt)
            ->withUpdatedAt($updatedUpdatedAt)
        ;

        $this->assertNotSame($title, $updatedPhotograph->title());
        $this->assertNotSame($description, $updatedPhotograph->description());
        $this->assertNotSame($filePath, $updatedPhotograph->filePath());
        $this->assertNotSame($createdAt, $updatedPhotograph->createdAt());
        $this->assertNotSame($updatedAt, $updatedPhotograph->updatedAt());

        $this->assertSame($uuid, $updatedPhotograph->uuid());
        $this->assertSame($updatedTitle, $updatedPhotograph->title());
        $this->assertSame($updatedDescription, $updatedPhotograph->description());
        $this->assertSame($updatedFilePath, $updatedPhotograph->filePath());
        $this->assertSame($updatedCreatedAt, $updatedPhotograph->createdAt());
        $this->assertSame($updatedUpdatedAt, $updatedPhotograph->updatedAt());
    }

    #[Test]
    public function applyOverwritesConstructorValues(): void
    {
        $uuid = $this->createStub(UUID::class);
        $title = $this->createStub(Title::class);
        $description = $this->createStub(Description::class);
        $filePath = $this->createStub(FilePath::class);
        $createdAt = $this->createStub(CreatedAt::class);
        $updatedAt = $this->createStub(UpdatedAt::class);

        $photographA = new Photograph(
            uuid: $uuid,
            title: $title,
            description: $description,
            filePath: $filePath,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
        );

        $constructorValueTitle = $this->createStub(Title::class);
        $constructorValueDescription = $this->createStub(Description::class);
        $constructorValueFilePath = $this->createStub(FilePath::class);
        $constructorValueCreatedAt = $this->createStub(CreatedAt::class);
        $constructorValueUpdatedAt = $this->createStub(UpdatedAt::class);

        $photographB = new Photograph(
            uuid: $uuid,
            title: $constructorValueTitle,
            description: $constructorValueDescription,
            filePath: $constructorValueFilePath,
            createdAt: $constructorValueCreatedAt,
            updatedAt: $constructorValueUpdatedAt,
        );
        $photographB->apply($photographA);

        $this->assertSame($uuid, $photographB->uuid());
        $this->assertSame($photographA->uuid(), $photographB->uuid());

        $this->assertNotSame($constructorValueTitle, $photographB->title());
        $this->assertNotSame($constructorValueDescription, $photographB->description());
        $this->assertNotSame($constructorValueFilePath, $photographB->filePath());
        $this->assertNotSame($constructorValueCreatedAt, $photographB->createdAt());
        $this->assertNotSame($constructorValueUpdatedAt, $photographB->updatedAt());

        $this->assertSame($title, $photographB->title());
        $this->assertSame($description, $photographB->description());
        $this->assertSame($filePath, $photographB->filePath());
        $this->assertSame($createdAt, $photographB->createdAt());
        $this->assertSame($updatedAt, $photographB->updatedAt());
    }

    #[Test]
    public function canBeCreatedWithNullableValues(): void
    {
        $uuid = null;
        $title = $this->createStub(Title::class);
        $description = null;
        $filePath = $this->createStub(FilePath::class);
        $createdAt = $this->createStub(CreatedAt::class);
        $updatedAt = $this->createStub(UpdatedAt::class);

        $photograph = new Photograph(
            uuid: $uuid,
            title: $title,
            description: $description,
            filePath: $filePath,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
        );

        $this->assertNull($photograph->uuid());
        $this->assertNull($photograph->description());
    }
}
