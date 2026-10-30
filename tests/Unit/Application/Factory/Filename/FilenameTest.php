<?php

namespace App\Tests\Unit\Application\Factory\Filename;

use App\Application\Factory\Filename\Factory;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use UnexpectedValueException;

class FilenameTest extends TestCase
{
    #[Test]
    public function canCreateFilenameWhenUploadedFileCanGuessExtension(): void
    {
        $uuid = 'f00cc521-58b6-4c5c-a8e5-029b2c06e9bf';
        $extension = 'jpg';

        $uploadedFile = $this->createStub(UploadedFile::class);
        $uploadedFile
            ->method('guessExtension')
            ->willReturn($extension);

        $filename = new Factory()->createFilename($uuid, $uploadedFile);

        $expected = sprintf('%s.%s', $uuid, $extension);
        $this->assertSame($expected, $filename);
    }

    #[Test]
    public function canCreateFilenameWhenUploadedFileCanNotGuessExtensionButHasKnownClientMimeType(): void
    {
        $uuid = '7251abd0-939d-4c9e-b12a-50055b06f57f';
        $extension = 'jpg';
        $mimeType = 'image/jpeg';

        $uploadedFile = $this->createStub(UploadedFile::class);
        $uploadedFile
            ->method('guessExtension')
            ->willReturn(null);

        $uploadedFile
            ->method('getClientMimeType')
            ->willReturn($mimeType);

        $filename = new Factory()->createFilename($uuid, $uploadedFile);

        $expected = sprintf('%s.%s', $uuid, $extension);
        $this->assertSame($expected, $filename);
    }

    #[Test]
    public function noUUIDGivesOriginalName(): void
    {
        $uuid = null;
        $originalName = 'originalName';

        $uploadedFile = $this->createStub(UploadedFile::class);
        $uploadedFile
            ->method('getClientOriginalName')
            ->willReturn($originalName);

        $filename = new Factory()->createFilename($uuid, $uploadedFile);

        $this->assertSame($originalName, $filename);
    }

    #[Test]
    public function triggersExceptionWhenUploadedFileCanNotGuessExtensionAndHasUnknownClientMimeType(): void
    {
        $this->expectException(UnexpectedValueException::class);

        $uuid = 'e43c02ff-ddee-4aaf-9ed1-a715695a267b';
        $mimeType = 'unknown-mime-type';

        $uploadedFile = $this->createStub(UploadedFile::class);
        $uploadedFile
            ->method('guessExtension')
            ->willReturn(null);

        $uploadedFile
            ->method('getClientMimeType')
            ->willReturn($mimeType);

        new Factory()->createFilename($uuid, $uploadedFile);
    }
}
