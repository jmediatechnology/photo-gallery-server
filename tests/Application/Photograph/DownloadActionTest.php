<?php

declare(strict_types=1);

namespace App\Tests\Application\Photograph;

use App\Domain\Entity\Photograph;
use App\Domain\ValueObject\CreatedAt;
use App\Domain\ValueObject\FilePath;
use App\Domain\ValueObject\Title;
use App\Domain\ValueObject\UpdatedAt;
use App\Domain\ValueObject\UUID;
use App\Infrastructure\Symfony\Lock\PhotographDownloadSlots;
use App\Tests\Application\ApiTestCase;
use DateTimeImmutable;
use JsonException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Clock\Test\ClockSensitiveTrait;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Lock\LockInterface;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;
use ZipArchive;

final class DownloadActionTest extends ApiTestCase
{
    use ClockSensitiveTrait;

    private const string FIXTURE_IMAGE = '03e05f1b-3952-4edb-adbf-3e6e12e49958.jpg';

    private const string PHOTOGRAPH_UUID_A = '5c3f0e4e-8d2b-4b8e-9a51-2f6a7d1c9e01';
    private const string PHOTOGRAPH_UUID_B = '7a1d2c3b-4e5f-4a6b-8c7d-9e0f1a2b3c02';
    private const string PHOTOGRAPH_UUID_UNKNOWN = '9f8e7d6c-5b4a-4392-8170-6f5e4d3c2b03';

    private const string VISITOR_IP_A = '203.0.113.10';
    private const string VISITOR_IP_B = '198.51.100.20';

    private string $fixtureImagePath = '';

    /** @var list<string> */
    private array $copiedImagePaths = [];

    /** @var list<LockInterface> */
    private array $claimedDownloadSlots = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->fixtureImagePath = self::getProjectDir() . '/fixtures/images/' . self::FIXTURE_IMAGE;

        $rateLimiterCache = static::getContainer()->get('cache.rate_limiter');
        assert($rateLimiterCache instanceof CacheItemPoolInterface);

        $rateLimiterCache->clear();
    }

    protected function tearDown(): void
    {
        new Filesystem()->remove($this->copiedImagePaths);
        array_walk($this->claimedDownloadSlots, static fn (LockInterface $slot) => $slot->release());

        parent::tearDown();
    }

    #[Test]
    public function anonymousVisitorWithoutTokenCanDownloadSelectedPhotographsAsZipArchive(): void
    {
        self::mockTime('2026-10-11 15:39:00');

        $this->savePhotographWithImage(self::PHOTOGRAPH_UUID_A, 'Sunset');
        $this->savePhotographWithImage(self::PHOTOGRAPH_UUID_B, 'Night');

        $this->requestDownload([self::PHOTOGRAPH_UUID_A, self::PHOTOGRAPH_UUID_B]);

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('Content-Type', 'application/zip');
        self::assertResponseHeaderSame('Content-Disposition', 'attachment; filename=photographs-2026-10-11_15-39-00.zip');

        $archive = $this->client->getInternalResponse()->getContent();
        $fixtureImage = new Filesystem()->readFile(filename: $this->fixtureImagePath);

        self::assertResponseHeaderSame('Content-Length', (string) strlen($archive));
        self::assertSame(
            [
                'Sunset.jpg' => $fixtureImage,
                'Night.jpg' => $fixtureImage,
            ],
            self::extractZipArchive($archive),
        );
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function rejectsASelectionWithUnknownPhotographs(): void
    {
        $this->savePhotographWithImage(self::PHOTOGRAPH_UUID_A, 'Sunset');

        $this->requestDownload([self::PHOTOGRAPH_UUID_A, self::PHOTOGRAPH_UUID_B]);

        self::assertResponseStatusCodeSame(422);
        self::assertSame('Photographs not found: ' . self::PHOTOGRAPH_UUID_B . '. ', $this->getResponseErrors());
    }

    /**
     * @param array<string, string> $query
     * @throws JsonException
     */
    #[Test]
    #[DataProvider('provideInvalidSelection')]
    public function rejectsInvalidSelection(array $query, string $expectedError): void
    {
        $this->client->request(
            method: 'GET',
            uri: '/photographs/download',
            parameters: $query,
        );

        self::assertResponseStatusCodeSame(422);
        self::assertStringContainsString($expectedError, $this->getResponseErrors());
    }

    #[Test]
    public function failsBeforeStreamingWhenAPhotographFileIsMissing(): void
    {
        $this->savePhotograph(self::PHOTOGRAPH_UUID_A, 'Sunset');

        $this->expectException(HandlerFailedException::class);
        $this->expectExceptionMessage(sprintf(
            'Photograph file "%s/%s.jpg" is missing or not readable.',
            self::$publicImagesDir,
            self::PHOTOGRAPH_UUID_A,
        ));

        $this->client->catchExceptions(false);
        $this->requestDownload([self::PHOTOGRAPH_UUID_A]);
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function returnsTooManyRequestsAfterTwentyDownloadsFromOneVisitor(): void
    {
        $this->savePhotographWithImage(self::PHOTOGRAPH_UUID_A, 'Sunset');
        $this->exhaustDownloadLimitFor(self::VISITOR_IP_A);

        $this->requestDownload([self::PHOTOGRAPH_UUID_A], self::VISITOR_IP_A);

        self::assertResponseStatusCodeSame(429);
        self::assertSame('Too Many Requests', $this->getResponseErrors());
        self::assertGreaterThan(0, (int) $this->client->getResponse()->headers->get('Retry-After'));
    }

    #[Test]
    public function countsDownloadsPerVisitorBehindTheReverseProxy(): void
    {
        $this->savePhotographWithImage(self::PHOTOGRAPH_UUID_A, 'Sunset');
        $this->exhaustDownloadLimitFor(self::VISITOR_IP_A);

        $this->requestDownload([self::PHOTOGRAPH_UUID_A], self::VISITOR_IP_B);

        self::assertResponseIsSuccessful();
        self::assertSame(0, $this->getRemainingDownloadsFor(self::VISITOR_IP_A));
        self::assertSame(19, $this->getRemainingDownloadsFor(self::VISITOR_IP_B));
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function returnsServiceUnavailableWhenAllDownloadSlotsAreTaken(): void
    {
        $this->savePhotographWithImage(self::PHOTOGRAPH_UUID_A, 'Sunset');
        $this->claimAllDownloadSlots();

        $this->requestDownload([self::PHOTOGRAPH_UUID_A]);

        self::assertResponseStatusCodeSame(503);
        self::assertResponseHeaderSame('Retry-After', '30');
        self::assertSame('Too many downloads in progress. Try again in a moment', $this->getResponseErrors());
    }

    #[Test]
    public function releasesTheDownloadSlotOnceStreamingHasEnded(): void
    {
        $this->savePhotographWithImage(self::PHOTOGRAPH_UUID_A, 'Sunset');

        $this->requestDownload([self::PHOTOGRAPH_UUID_A]);
        self::assertResponseIsSuccessful();

        // Fails when the download kept its slot.
        $this->claimAllDownloadSlots();
    }

    /**
     * @param list<string> $uuids
     */
    private function requestDownload(array $uuids, ?string $clientIp = null): void
    {
        $this->client->request(
            method: 'GET',
            uri: '/photographs/download?uuids=' . implode(',', $uuids),
            server: $clientIp === null ? [] : ['HTTP_X_FORWARDED_FOR' => $clientIp],
        );
    }

    /**
     * Uses up the visitor's 20 downloads per hour with requests that are rejected before streaming.
     */
    private function exhaustDownloadLimitFor(string $clientIp): void
    {
        for ($download = 1; $download <= 20; $download++) {
            $this->requestDownload([self::PHOTOGRAPH_UUID_UNKNOWN], $clientIp);
            self::assertResponseStatusCodeSame(422);
        }
    }

    private function claimAllDownloadSlots(): void
    {
        $downloadSlots = static::getContainer()->get(PhotographDownloadSlots::class);
        assert($downloadSlots instanceof PhotographDownloadSlots);

        for ($slot = 1; $slot <= PhotographDownloadSlots::COUNT; $slot++) {
            $claimedDownloadSlot = $downloadSlots->claim();
            self::assertNotNull(
                $claimedDownloadSlot,
                sprintf('Download slot %d of %d was already taken.', $slot, PhotographDownloadSlots::COUNT),
            );

            $this->claimedDownloadSlots[] = $claimedDownloadSlot;
        }
    }

    private function savePhotographWithImage(string $uuid, string $title): void
    {
        $this->savePhotograph($uuid, $title);

        $imagePath = self::$publicImagesDir . '/' . $uuid . '.jpg';
        new Filesystem()->copy(
            originFile: $this->fixtureImagePath,
            targetFile: $imagePath,
        );
        $this->copiedImagePaths[] = $imagePath;
    }

    private function savePhotograph(string $uuid, string $title): void
    {
        $this->photographRepository->save(new Photograph(
            uuid: new UUID($uuid),
            title: new Title($title),
            description: null,
            filePath: new FilePath('/images/' . $uuid . '.jpg'),
            createdAt: new CreatedAt(new DateTimeImmutable()),
            updatedAt: new UpdatedAt(new DateTimeImmutable()),
        ));
    }

    private function getRemainingDownloadsFor(string $clientIp): int
    {
        $limiterFactory = static::getContainer()->get('limiter.photograph_download');
        assert($limiterFactory instanceof RateLimiterFactoryInterface);

        // #[RateLimit] keys on client IP, HTTP method and path.
        $key = $clientIp . '~GET~/photographs/download';

        // Consuming zero tokens reads the state without changing it.
        return $limiterFactory->create($key)->consume(0)->getRemainingTokens();
    }

    /**
     * @return array<string, string> entry name => contents
     */
    private static function extractZipArchive(string $archive): array
    {
        $archivePath = tempnam(directory: sys_get_temp_dir(), prefix: 'download-test-');
        new Filesystem()->dumpFile(filename: $archivePath, content: $archive);

        $zip = new ZipArchive();
        self::assertTrue($zip->open(filename: $archivePath, flags: ZipArchive::CHECKCONS));

        $entries = [];
        for ($index = 0; $index < $zip->numFiles; $index++) {
            $name = $zip->getNameIndex($index);
            $contents = $zip->getFromIndex($index);
            $entries[$name] = $contents;
        }

        $zip->close();
        new Filesystem()->remove(files: $archivePath);

        return $entries;
    }

    /**
     * @throws JsonException
     */
    private function getResponseErrors(): string
    {
        $json = json_decode(
            json: $this->client->getResponse()->getContent(),
            associative: true,
            flags: JSON_THROW_ON_ERROR,
        );

        return $json['errors'];
    }

    /**
     * @return array<string, array{array<string, string>, string}>
     */
    public static function provideInvalidSelection(): array
    {
        $tooManyUuids = array_map(
            callback: static fn (int $number): string => sprintf('00000000-0000-4000-8000-%012d', $number),
            array: range(1, 101),
        );

        return [
            'no uuids parameter' => [[], 'Select at least one photograph to download'],
            'empty uuids parameter' => [['uuids' => ' , '], 'Select at least one photograph to download'],
            'malformed uuid' => [['uuids' => 'not-a-uuid'], '"not-a-uuid" is not a valid photograph id'],
            'more than 100 photographs' => [['uuids' => implode(',', $tooManyUuids)], 'Select at most 100 photographs to download'],
        ];
    }
}
