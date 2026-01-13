<?php

namespace App\Application\Factory\FilePath;

use App\Domain\ValueObject\FilePath;

class Factory
{
    public function __construct(
        private string $imagesDir,
    ) {}

    public function create(string $filename): FilePath
    {
        $relativeFilePath = $this->getRelativeFilePath($filename);
        return new FilePath($relativeFilePath);
    }

    private function getRelativeFilePath(string $filename): string
    {
        return strtr(
            ':imagesDir/:filename',
            [
                ':imagesDir' => $this->imagesDir,
                ':filename' => $filename,
            ],
        );
    }
}
