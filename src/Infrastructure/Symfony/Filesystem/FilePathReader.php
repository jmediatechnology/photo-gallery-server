<?php

namespace App\Infrastructure\Symfony\Filesystem;

use App\Domain\ValueObject\FilePath;
use RuntimeException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Mime\MimeTypeGuesserInterface;

class FilePathReader implements FilePathReaderInterface
{
    public function __construct(
        private string $publicImageDir,
        private Filesystem $filesystem,
        private MimeTypeGuesserInterface $mimeTypeGuesser,
    ) {}

    /**
     * @return string the raw file contents (binary), not base64
     */
    public function read(FilePath $filePath): string
    {
        $filename = $this->resolveFilename($filePath);

        return $this->filesystem->readFile($filename);
    }

    /**
     * Detects the MIME type from the file contents, not from the extension.
     */
    public function mimeType(FilePath $filePath): string
    {
        $filename = $this->resolveFilename($filePath);

        $mimeType = $this->mimeTypeGuesser->guessMimeType($filename);
        if ($mimeType === null) {
            throw new RuntimeException(sprintf('Unable to determine the MIME type of "%s".', $filename));
        }

        return $mimeType;
    }

    private function resolveFilename(FilePath $filePath): string
    {
        $filename = $this->publicImageDir . '/' . $filePath->getBasename();
        if (!is_file($filename) || !is_readable($filename)) {
            throw new RuntimeException(sprintf('File "%s" does not exist or is not readable.', $filename));
        }

        return $filename;
    }
}
