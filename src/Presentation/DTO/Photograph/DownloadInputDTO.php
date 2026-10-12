<?php

namespace App\Presentation\DTO\Photograph;

class DownloadInputDTO
{
    /**
     * Keeps the request URL under Apache's ~8 KB request-line limit (100 × 37 characters ≈ 3.7 KB)
     * and bounds the size of a single download.
     * The client mirrors this number in photographDownload.ts.
     */
    public const int MAX_PHOTOGRAPHS = 100;

    /**
     * @param list<string> $uuids
     */
    public function __construct(
        private array $uuids,
    ) {}

    /**
     * @return list<string>
     */
    public function uuids(): array
    {
        return $this->uuids;
    }
}
