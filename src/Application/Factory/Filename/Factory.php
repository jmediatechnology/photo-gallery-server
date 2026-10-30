<?php

namespace App\Application\Factory\Filename;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use UnexpectedValueException;

class Factory
{
    private const array MIME_TYPE_EXTENSIONS_MAP = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    ];

    public function createFilename(?string $uuid, UploadedFile $uploadedFile): string
    {
        if ($uuid === null) {
            return $uploadedFile->getClientOriginalName();
        }

        $extension = $uploadedFile->guessExtension();
        if ($extension === null) {
            $mimeType = $uploadedFile->getClientMimeType();
            $extension = (string)(self::MIME_TYPE_EXTENSIONS_MAP[$mimeType] ?? '') ?: null;
        }

        if ($extension === null) {
            throw new UnexpectedValueException($mimeType);
        }

        return sprintf('%s.%s', $uuid, $extension);
    }
}
