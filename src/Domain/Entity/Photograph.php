<?php

namespace App\Domain\Entity;

use App\Domain\ValueObject\CreatedAt;
use App\Domain\ValueObject\Description;
use App\Domain\ValueObject\FilePath;
use App\Domain\ValueObject\Title;
use App\Domain\ValueObject\UpdatedAt;
use App\Domain\ValueObject\UUID;

class Photograph
{
    public function __construct(
        private ?UUID        $uuid = null,
        private Title        $title,
        private ?Description $description = null,
        private FilePath     $filePath,
        private CreatedAt    $createdAt,
        private UpdatedAt    $updatedAt,
    ) {}

    public function uuid(): ?UUID
    {
        return $this->uuid;
    }

    public function title(): Title
    {
        return $this->title;
    }

    public function withTitle(Title $title): self
    {
        return new static(
            uuid: $this->uuid,
            title: $title,
            description: $this->description,
            filePath: $this->filePath,
            createdAt: $this->createdAt,
            updatedAt: $this->updatedAt,
        );
    }

    public function description(): ?Description
    {
        return $this->description;
    }

    public function withDescription(?Description $description): self
    {
        return new static(
            uuid: $this->uuid,
            title: $this->title,
            description: $description,
            filePath: $this->filePath,
            createdAt: $this->createdAt,
            updatedAt: $this->updatedAt,
        );
    }

    public function filePath(): ?FilePath
    {
        return $this->filePath;
    }

    public function withFilePath(FilePath $filePath): self
    {
        return new static(
            uuid: $this->uuid,
            title: $this->title,
            description: $this->description,
            filePath: $filePath,
            createdAt: $this->createdAt,
            updatedAt: $this->updatedAt,
        );
    }

    public function createdAt(): CreatedAt
    {
        return $this->createdAt;
    }

    public function withCreatedAt(CreatedAt $createdAt): self
    {
        return new static(
            uuid: $this->uuid,
            title: $this->title,
            description: $this->description,
            filePath: $this->filePath,
            createdAt: $createdAt,
            updatedAt: $this->updatedAt,
        );
    }

    public function updatedAt(): UpdatedAt
    {
        return $this->updatedAt;
    }

    public function withUpdatedAt(UpdatedAt $updatedAt): self
    {
        return new static(
            uuid: $this->uuid,
            title: $this->title,
            description: $this->description,
            filePath: $this->filePath,
            createdAt: $this->createdAt,
            updatedAt: $updatedAt,
        );
    }

    public function apply(Photograph $photograph): void
    {
        $this->title = $photograph->title();
        $this->description = $photograph->description();
        $this->filePath = $photograph->filePath();
        $this->createdAt = $photograph->createdAt();
        $this->updatedAt = $photograph->updatedAt();
    }
}
