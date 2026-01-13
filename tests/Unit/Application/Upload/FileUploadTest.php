<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Upload;

use App\Application\Upload\FileUploader;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploadTest extends TestCase
{
    #[Test]
    public function throwsFileNotFoundExceptionWhenMoveOperationFailed(): void
    {
        $this->expectException(FileNotFoundException::class);

        $filename = 'unknown_file.jpg';
        $publicImageDir = '/';

        $file = $this->createMock(UploadedFile::class);
        $file
            ->expects($this->once())
            ->method('move')
            ->with($publicImageDir, $filename);


        new FileUploader('/')->upload($file, $filename);
    }
}
