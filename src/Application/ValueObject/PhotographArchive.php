<?php

namespace App\Application\ValueObject;

/**
 * One file inside a downloadable archive: what it is called there, where it is read from, and how big it is.
 */
readonly class PhotographArchive
{
    /**
     * @param array<ArchiveEntry> $entries
     */
    public function __construct(
        private string $name,
        private array $entries,
    ) {}

    public function name(): string
    {
        return $this->name;
    }

    /**
     * @return array<ArchiveEntry>
     */
    public function entries(): array
    {
        return $this->entries;
    }
}
