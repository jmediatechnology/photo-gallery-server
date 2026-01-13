<?php

namespace App\Presentation\DTO\Photograph;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class InputDTO
{
    public function __construct(
        private ?string $uuid = null,
        private string $title,
        private ?string $description = null,
        private UploadedFile $file,
    ) {
    }

    public function uuid(): ?string
    {
        return $this->uuid;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    public function file(): UploadedFile
    {
        return $this->file;
    }

    public static function create(
        ?string $uuid,
        string $title,
        ?string $description,
        UploadedFile $file
    ): self
    {
        return new self(
            uuid: $uuid,
            title: $title,
            description: $description,
            file: $file,
        );
    }
}
