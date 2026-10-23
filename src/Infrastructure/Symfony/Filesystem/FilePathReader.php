<?php

namespace App\Infrastructure\Symfony\Filesystem;

use App\Domain\ValueObject\FilePath;
use RuntimeException;
use Symfony\Component\Filesystem\Filesystem;

class FilePathReader implements FilePathReaderInterface
{
    public function __construct(
        private string $publicImageDir,
    ) {}

    public function read(FilePath $filePath): string
    {
        $filename = $this->resolveFilename($filePath);

        $fileSystem = new Filesystem();
        $contents = $fileSystem->readFile($filename);

        return base64_encode($contents);
    }

    public function mimeType(FilePath $filePath): string
    {
        $filename = $this->resolveFilename($filePath);

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo === false) {
            throw new RuntimeException('Unable to open fileinfo.'); // @codeCoverageIgnore
        }

        $mimeType = finfo_file($finfo, $filename);
        if ($mimeType === false) {
            throw new RuntimeException(sprintf('Unable to determine MIME type for "%s".', $filename)); // @codeCoverageIgnore
        }

        return $mimeType;
    }

    private function resolveFilename(FilePath $filePath): string
    {
        $filename = $this->publicImageDir . '/' . $filePath->getBasename();
        if (!file_exists($filename)) {
            throw new RuntimeException(sprintf('File %s does not exist.', $filename));
        }

        return $filename;
    }
}
