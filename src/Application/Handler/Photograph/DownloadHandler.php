<?php

namespace App\Application\Handler\Photograph;

use App\Application\Factory\ArchiveEntry\Factory as ArchiveEntryFactory;
use App\Application\Query\Photograph\DownloadQuery;
use App\Application\ValueObject\PhotographArchive;
use App\Domain\Repository\PhotographRepositoryInterface;
use App\Domain\ValueObject\UUID;
use Psr\Clock\ClockInterface;

class DownloadHandler
{
    private const string ARCHIVE_NAME_FORMAT = 'photographs-%s.zip';
    private const string ARCHIVE_NAME_DATE_TIME_FORMAT = 'Y-m-d_H-i-s';

    public function __construct(
        private PhotographRepositoryInterface $photographRepository,
        private ArchiveEntryFactory $archiveEntryFactory,
        private ClockInterface $clock,
    ) {}

    /**
     * Expects a validated query: DownloadForKnownPhotographUuidsConstraint has checked that every photograph exists.
     */
    public function __invoke(DownloadQuery $query): PhotographArchive
    {
        $uuids = array_map(
            callback: static fn (string $uuid): UUID => new UUID($uuid),
            array: $query->uuids(),
        );

        $photographs = $this->photographRepository->findByUUIDs($uuids);

        return new PhotographArchive(
            name: sprintf(
                self::ARCHIVE_NAME_FORMAT,
                $this->clock->now()->format(format: self::ARCHIVE_NAME_DATE_TIME_FORMAT)
            ),
            entries: $this->archiveEntryFactory->createForPhotographs($photographs),
        );
    }
}
