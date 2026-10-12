<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\ZipStream;

use App\Application\ValueObject\ArchiveEntry;
use App\Application\ValueObject\PhotographArchive;
use App\Infrastructure\ZipStream\ZipArchiveWriter;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use ZipArchive;
use ZipStream\Exception\FileNotFoundException;
use ZipStream\Exception\FileSizeIncorrectException;

final class ZipArchiveWriterTest extends TestCase
{
    private const string SUNSET_CONTENT = 'sunset-bytes';
    private const string NIGHT_CONTENT = 'night-bytes-night-bytes';

    private string $directory;
    private ZipArchiveWriter $writer;

    protected function setUp(): void
    {
        $this->directory = sys_get_temp_dir() . '/zip-archive-writer-test-' . bin2hex(random_bytes(4));
        mkdir($this->directory);
        file_put_contents($this->directory . '/sunset.jpg', self::SUNSET_CONTENT);
        file_put_contents($this->directory . '/night.jpg', self::NIGHT_CONTENT);
        file_put_contents($this->directory . '/empty.jpg', '');

        $this->writer = new ZipArchiveWriter();
    }

    protected function tearDown(): void
    {
        array_map(unlink(...), glob($this->directory . '/*') ?: []);
        rmdir($this->directory);
    }

    #[Test]
    public function streamsEveryEntryUnderItsArchiveName(): void
    {
        $zip = $this->openZip($this->captureStream($this->createArchive()));

        self::assertSame(3, $zip->numFiles);
        self::assertSame(self::SUNSET_CONTENT, $zip->getFromName('Zonsondergang über Almere.jpg'));
        self::assertSame(self::NIGHT_CONTENT, $zip->getFromName('Night.jpg'));
        self::assertSame('', $zip->getFromName('Empty.jpg'));
    }

    #[Test]
    public function storesTheEntriesWithoutCompression(): void
    {
        $zip = $this->openZip($this->captureStream($this->createArchive()));

        for ($index = 0; $index < $zip->numFiles; $index++) {
            self::assertSame(ZipArchive::CM_STORE, $zip->statIndex($index)['comp_method']);
        }
    }

    #[Test]
    public function calculatesTheExactSizeOfTheStreamedArchive(): void
    {
        $archive = $this->createArchive();

        self::assertSame(strlen($this->captureStream($archive)), $this->writer->size($archive));
    }

    #[Test]
    public function translatesAMissingFileWhileCalculatingTheSize(): void
    {
        $archive = $this->createArchiveWith($this->createEntry('Missing.jpg', 'missing.jpg', 10));

        $exception = $this->catchException(fn () => $this->writer->size($archive));

        self::assertSame('Unable to prepare the archive.', $exception->getMessage());
        self::assertInstanceOf(FileNotFoundException::class, $exception->getPrevious());
    }

    #[Test]
    public function translatesAMissingFileWhileStreaming(): void
    {
        $archive = $this->createArchiveWith($this->createEntry('Missing.jpg', 'missing.jpg', 10));

        $exception = $this->catchException(fn () => $this->captureStream($archive));

        self::assertSame('Unable to stream the archive.', $exception->getMessage());
        self::assertInstanceOf(FileNotFoundException::class, $exception->getPrevious());
    }

    #[Test]
    public function translatesAFileThatChangedSizeWhileStreaming(): void
    {
        $changedSize = strlen(self::SUNSET_CONTENT) + 1;
        $archive = $this->createArchiveWith($this->createEntry('Sunset.jpg', 'sunset.jpg', $changedSize));

        $exception = $this->catchException(fn () => $this->captureStream($archive));

        self::assertSame('Unable to stream the archive.', $exception->getMessage());
        self::assertInstanceOf(FileSizeIncorrectException::class, $exception->getPrevious());
    }

    private function createArchive(): PhotographArchive
    {
        return $this->createArchiveWith(
            $this->createEntry('Zonsondergang über Almere.jpg', 'sunset.jpg', strlen(self::SUNSET_CONTENT)),
            $this->createEntry('Night.jpg', 'night.jpg', strlen(self::NIGHT_CONTENT)),
            $this->createEntry('Empty.jpg', 'empty.jpg', 0),
        );
    }

    private function createArchiveWith(ArchiveEntry ...$entries): PhotographArchive
    {
        return new PhotographArchive(name: 'photographs.zip', entries: array_values($entries));
    }

    private function createEntry(string $name, string $fileName, int $size): ArchiveEntry
    {
        return new ArchiveEntry(name: $name, path: $this->directory . '/' . $fileName, size: $size);
    }

    private function captureStream(PhotographArchive $archive): string
    {
        ob_start();
        try {
            $this->writer->stream($archive);
        } finally {
            $output = (string) ob_get_clean();
        }

        return $output;
    }

    private function openZip(string $bytes): ZipArchive
    {
        $path = $this->directory . '/streamed.zip';
        file_put_contents($path, $bytes);

        $zip = new ZipArchive();
        self::assertTrue($zip->open($path, ZipArchive::CHECKCONS));

        return $zip;
    }

    private function catchException(callable $callable): RuntimeException
    {
        try {
            $callable();
        } catch (RuntimeException $exception) {
            return $exception;
        }

        self::fail('Expected a RuntimeException, but none was thrown.');
    }
}
