<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Handlers\Photograph;

use App\Application\Factory\ArchiveEntry\Factory as ArchiveEntryFactory;
use App\Application\Handler\Photograph\DownloadHandler;
use App\Application\Query\Photograph\DownloadQuery;
use App\Application\ValueObject\ArchiveEntry;
use App\Domain\Entity\Photograph;
use App\Domain\ValueObject\UUID;
use App\Infrastructure\Doctrine\Repository\PhotographRepository;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\MockClock;

final class DownloadHandlerTest extends TestCase
{
    private const string SUNSET_UUID = '5c3f0e4e-8d2b-4b8e-9a51-2f6a7d1c9e01';
    private const string NIGHT_UUID = '7a1d2c3b-4e5f-4a6b-8c7d-9e0f1a2b3c02';

    #[Test]
    public function archivesThePhotographsWithTheRequestedUuids(): void
    {
        $photographs = [
            $this->createStub(Photograph::class),
            $this->createStub(Photograph::class),
        ];
        $entries = [
            new ArchiveEntry(name: 'Sunset.jpg', path: '/images/sunset.jpg', size: 1024),
            new ArchiveEntry(name: 'Night.jpg', path: '/images/night.jpg', size: 2048),
        ];

        $photographRepository = $this->createMock(PhotographRepository::class);
        $photographRepository
            ->expects($this->once())
            ->method('findByUUIDs')
            ->with([new UUID(self::SUNSET_UUID), new UUID(self::NIGHT_UUID)])
            ->willReturn($photographs);

        $archiveEntryFactory = $this->createMock(ArchiveEntryFactory::class);
        $archiveEntryFactory
            ->expects($this->once())
            ->method('createForPhotographs')
            ->with($this->identicalTo($photographs))
            ->willReturn($entries);

        $archive = new DownloadHandler(
            $photographRepository,
            $archiveEntryFactory,
            new MockClock(),
        )->__invoke(new DownloadQuery(uuids: [self::SUNSET_UUID, self::NIGHT_UUID]));

        self::assertSame($entries, $archive->entries());
    }

    #[Test]
    public function namesTheArchiveAfterTheMomentOfTheDownload(): void
    {
        $archive = new DownloadHandler(
            $this->createStub(PhotographRepository::class),
            $this->createStub(ArchiveEntryFactory::class),
            new MockClock('2026-10-11 15:39:00'),
        )->__invoke(new DownloadQuery(uuids: [self::SUNSET_UUID]));

        self::assertSame('photographs-2026-10-11_15-39-00.zip', $archive->name());
    }
}
