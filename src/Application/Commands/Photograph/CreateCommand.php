<?php

namespace App\Application\Commands\Photograph;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class CreateCommand
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
}
