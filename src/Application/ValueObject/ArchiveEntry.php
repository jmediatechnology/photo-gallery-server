<?php

namespace App\Application\ValueObject;

/**
 * One file inside a downloadable archive: what it is called there, where it is read from, and how big it is.
 */
readonly class ArchiveEntry
{
    public function __construct(
        private string $name,
        private string $path,
        private int $size,
    ) {}

    public function name(): string
    {
        return $this->name;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function size(): int
    {
        return $this->size;
    }
}
