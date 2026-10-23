<?php

namespace App\Infrastructure\Symfony\Filesystem;

use App\Domain\ValueObject\FilePath;

interface FilePathReaderInterface
{
    public function read(FilePath $filePath): string;
    public function mimeType(FilePath $filePath): string;
}
