<?php

namespace App\Application\Factory\ArchiveEntry;

use App\Application\ValueObject\ArchiveEntry;
use App\Domain\Entity\Photograph;
use RuntimeException;
use SplFileInfo;

/**
 * Turns photographs into archive entries named after their titles.
 *
 * Everything that can fail does so here, before a single byte of the archive is sent:
 * once streaming starts, the HTTP status is fixed at 200.
 */
class Factory
{
    /**
     * Path separators would let a title like "../../x" escape the extraction folder (zip slip).
     * Control characters and the rest are rejected by Windows file systems.
     */
    private const string FORBIDDEN_CHARACTERS = '/[\x00-\x1F\x7F\/\\\\:*?"<>|]/';
    private const string REPLACEMENT_CHARACTER = '_';

    /**
     * Windows drops trailing dots and spaces; a leading dot hides the file on macOS and Linux.
     */
    private const string CHARACTERS_TO_TRIM = ' .';

    public function __construct(
        private string $publicImageDir,
    ) {}

    /**
     * @param array<Photograph> $photographs
     * @return array<ArchiveEntry>
     */
    public function createForPhotographs(array $photographs): array
    {
        $entries = [];
        $takenNames = [];

        foreach ($photographs as $photograph) {
            $path = $this->resolveReadablePath($photograph);
            $name = $this->createUniqueName($photograph, $takenNames);
            $takenNames[] = mb_strtolower($name);

            $entries[] = new ArchiveEntry(
                name: $name,
                path: $path,
                size: new SplFileInfo($path)->getSize(),
            );
        }

        return $entries;
    }

    private function resolveReadablePath(Photograph $photograph): string
    {
        $path = $this->publicImageDir . '/' . $photograph->filePath()->getBasename();
        if (!is_readable($path)) {
            throw new RuntimeException(sprintf('Photograph file "%s" is missing or not readable.', $path));
        }

        return $path;
    }

    /**
     * Two titles can end up with the same name after sanitising, and most desktops compare file names
     * case-insensitively, so "Sunset.jpg" and "sunset.jpg" would overwrite each other on extraction.
     *
     * @param array<string> $takenNames lowercased
     */
    private function createUniqueName(Photograph $photograph, array $takenNames): string
    {
        $basename = $photograph->filePath()->getBasename();
        $filename = pathinfo($basename, PATHINFO_FILENAME);

        $title = $this->sanitize(
            title: $photograph->title()->__toString(),
            fallback: $filename,
        );
        $extension = pathinfo($basename, PATHINFO_EXTENSION);

        $name = $this->appendExtension($title, $extension);
        for ($copyNumber = 2; in_array(mb_strtolower($name), $takenNames, true); $copyNumber++) {
            $name = $this->appendExtension(sprintf('%s (%d)', $title, $copyNumber), $extension);
        }

        return $name;
    }

    private function sanitize(string $title, string $fallback): string
    {
        $sanitized = preg_replace(self::FORBIDDEN_CHARACTERS, self::REPLACEMENT_CHARACTER, $title);
        $trimmed = trim((string) $sanitized, self::CHARACTERS_TO_TRIM);

        return $trimmed === '' ? $fallback : $trimmed;
    }

    private function appendExtension(string $title, string $extension): string
    {
        return $extension === '' ? $title : $title . '.' . $extension;
    }
}
