<?php

namespace App\Application\Upload;

use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploader
{
    public function __construct(
        private string $publicImageDir,
    ) {
    }

    public function upload(UploadedFile $file, string $fileName): void
    {
        $file->move(
            directory: $this->publicImageDir,
            name: $fileName,
        );

        if (!file_exists($this->publicImageDir . '/' . $fileName)) {
            throw new FileNotFoundException('Failed to upload file: ' . $fileName . '.');
        }
    }
}
