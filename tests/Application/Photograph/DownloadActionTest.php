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

class DownloadActionTest extends ApiTestCase
{
    use ClockSensitiveTrait;

    private const string FIXTURE_IMAGE = '03e05f1b-3952-4edb-adbf-3e6e12e49958.jpg';

    private const string SUNSET_UUID = '5c3f0e4e-8d2b-4b8e-9a51-2f6a7d1c9e01';
    private const string NIGHT_UUID = '7a1d2c3b-4e5f-4a6b-8c7d-9e0f1a2b3c02';
    private const string UNKNOWN_UUID = '9f8e7d6c-5b4a-4392-8170-6f5e4d3c2b03';

    private string $fixtureImagePath = '';


    /** @var list<string> */
    private array $copiedImagePaths = [];

    /** @var list<LockInterface> */
    private array $downloadLocks = [];

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
        array_walk($this->downloadLocks, static fn (LockInterface $slot) => $slot->release());

        parent::tearDown();
    }

    #[Test]
    public function anonymousVisitorWithoutTokenCanDownloadSelectedPhotographsAsZipArchive(): void
    {
        self::mockTime('2026-10-11 15:39:00');

        $this->savePhotographWithImage(self::SUNSET_UUID, 'Sunset');
        $this->savePhotographWithImage(self::NIGHT_UUID, 'Night');

        $this->client->request(
            method: 'GET',
            uri: '/photographs/download?uuids=' . implode(',', [self::SUNSET_UUID, self::NIGHT_UUID])
        );

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('Content-Type', 'application/zip');
        self::assertResponseHeaderSame('Content-Disposition', 'attachment; filename=photographs-2026-10-11_15-39-00.zip');

        $archive = $this->client->getInternalResponse()->getContent();

        self::assertResponseHeaderSame('Content-Length', (string) strlen($archive));
        self::assertSame(
            [
                'Sunset.jpg' => new Filesystem()->readFile(filename: $this->fixtureImagePath),
                'Night.jpg' => new Filesystem()->readFile(filename: $this->fixtureImagePath),
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
        $this->savePhotographWithImage(self::SUNSET_UUID, 'Sunset');

        $this->client->request(
            method: 'GET',
            uri: '/photographs/download?uuids=' . implode(',', [self::SUNSET_UUID, self::NIGHT_UUID])
        );

        self::assertResponseStatusCodeSame(422);

        $json = json_decode(
            json: $this->client->getResponse()->getContent(),
            associative: true,
            flags: JSON_THROW_ON_ERROR
        );
        $errors = $json['errors'];

        self::assertSame('Photographs not found: ' . self::NIGHT_UUID  . '. ', $errors);
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
            parameters: $query
        );

        self::assertResponseStatusCodeSame(422);

        $json = json_decode(
            json: $this->client->getResponse()->getContent(),
            associative: true,
            flags: JSON_THROW_ON_ERROR
        );
        $errors = $json['errors'];

        self::assertStringContainsString($expectedError, $errors);
    }

    #[Test]
    public function failsBeforeStreamingWhenAPhotographFileIsMissing(): void
    {
        $this->savePhotograph(self::SUNSET_UUID, 'Sunset');

        $this->expectException(HandlerFailedException::class);
        $this->expectExceptionMessage(sprintf(
            'Photograph file "%s/%s.jpg" is missing or not readable.',
            self::$publicImagesDir,
            self::SUNSET_UUID,
        ));

        $this->client->catchExceptions(false);
        $this->client->request(
            method: 'GET',
            uri: '/photographs/download?uuids=' . implode(',', [self::SUNSET_UUID])
        );
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function returnsTooManyRequestsAfterTwentyDownloadsFromOneVisitor(): void
    {
        $this->savePhotographWithImage(self::SUNSET_UUID, 'Sunset');

        $this->downloadNTimesAs(
            amountOfDownloads: 20,
            clientIp: '203.0.113.10'
        );

        $this->client->request(
            method: 'GET',
            uri: '/photographs/download?uuids=' . implode(',', [self::SUNSET_UUID]),
            server: ['HTTP_X_FORWARDED_FOR' => '203.0.113.10'],
        );

        $json = json_decode(
            json: $this->client->getResponse()->getContent(),
            associative: true,
            flags: JSON_THROW_ON_ERROR
        );
        $errors = $json['errors'];

        self::assertResponseStatusCodeSame(429);
        self::assertSame('Too Many Requests', $errors);
        self::assertGreaterThan(0, (int) $this->client->getResponse()->headers->get('Retry-After'));
    }

    #[Test]
    public function countsDownloadsPerVisitorBehindTheReverseProxy(): void
    {
        $this->savePhotographWithImage(self::SUNSET_UUID, 'Sunset');

        $this->downloadNTimesAs(
            amountOfDownloads: 20,
            clientIp: '203.0.113.10'
        );

        $this->client->request(
            method: 'GET',
            uri: '/photographs/download?uuids=' . implode(',', [self::SUNSET_UUID]),
            server: ['HTTP_X_FORWARDED_FOR' => '198.51.100.20'],
        );

        self::assertResponseIsSuccessful();
        self::assertSame(0, $this->getRemainingDownloadsFor('203.0.113.10'));
        self::assertSame(19, $this->getRemainingDownloadsFor('198.51.100.20'));
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function returnsServiceUnavailableWhenAllAvailableDownloadLocksAreExhausted(): void
    {
        $this->savePhotographWithImage(self::SUNSET_UUID, 'Sunset');
        $this->claimDownloadLocks();

        $this->client->request(
            method: 'GET',
            uri: '/photographs/download?uuids=' . implode(',', [self::SUNSET_UUID])
        );

        self::assertResponseStatusCodeSame(503);

        $json = json_decode(
            json: $this->client->getResponse()->getContent(),
            associative: true,
            flags: JSON_THROW_ON_ERROR
        );
        $errors = $json['errors'];

        self::assertResponseHeaderSame('Retry-After', '30');
        self::assertSame('Too many downloads in progress. Try again in a moment', $errors);
    }

    #[Test]
    public function releasesTheDownloadLocksOnceStreamingHasEnded(): void
    {
        $this->savePhotographWithImage(self::SUNSET_UUID, 'Sunset');

        $this->client->request(
            method: 'GET',
            uri: '/photographs/download?uuids=' . implode(',', [self::SUNSET_UUID])
        );
        self::assertResponseIsSuccessful();

        $this->claimDownloadLocks();
        self::assertNotContains(null, $this->downloadLocks);
    }

    private function downloadNTimesAs(int $amountOfDownloads, string $clientIp): void
    {
        for ($download = 1; $download <= $amountOfDownloads; $download++) {
            $this->client->request(
                method: 'GET',
                uri: '/photographs/download?uuids=' . implode(',', [self::UNKNOWN_UUID]),
                server: ['HTTP_X_FORWARDED_FOR' => $clientIp],
            );
            self::assertResponseStatusCodeSame(422);
        }
    }

    private function claimDownloadLocks(): void
    {
        $downloadSlots = static::getContainer()->get(PhotographDownloadSlots::class);
        assert($downloadSlots instanceof PhotographDownloadSlots);

        for ($slot = 1; $slot <= PhotographDownloadSlots::COUNT; $slot++) {
            $this->downloadLocks[] = $downloadSlots->claim();
        }
    }

    private function savePhotographWithImage(string $uuid, string $title): void
    {
        $this->savePhotograph($uuid, $title);

        $imagePath = self::$publicImagesDir . '/' . $uuid . '.jpg';
        new Filesystem()->copy(
            originFile: $this->fixtureImagePath,
            targetFile: $imagePath
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
