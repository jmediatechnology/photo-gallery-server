<?php

namespace App\Tests\Utils\ObjectMother;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploadedFileMother
{
    public static function create(string $path, string $originalName): UploadedFile
    {
        return new UploadedFile(
            path: $path,
            originalName: $originalName,
        );
    }
}
