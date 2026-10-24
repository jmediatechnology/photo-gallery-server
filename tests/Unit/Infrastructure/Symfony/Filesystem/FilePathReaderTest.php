<?php

namespace App\Tests\Unit\Infrastructure\Symfony\Filesystem;

use App\Domain\ValueObject\FilePath;
use App\Infrastructure\Symfony\Filesystem\FilePathReader;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Mime\MimeTypeGuesserInterface;

class FilePathReaderTest extends TestCase
{
    private const string TEST_IMAGE_FILENAME = 'test-image-solina-150x150.jpg';
    private const string TEST_IMAGE_FULL_PATH = __DIR__ . '/' . self::TEST_IMAGE_FILENAME;

    private string $publicImageDir;

    protected function setUp(): void
    {
        $this->publicImageDir = sys_get_temp_dir() . '/file-path-reader-test-' . uniqid('', true);

        new Filesystem()->copy(
            originFile: self::TEST_IMAGE_FULL_PATH,
            targetFile: $this->publicImageDir . '/' . self::TEST_IMAGE_FILENAME,
        );
    }

    protected function tearDown(): void
    {
        new Filesystem()->remove($this->publicImageDir);
    }

    #[Test]
    public function throwsExceptionWhenWhenFileDoesNotExists(): void
    {
        $basename = 'non-existing-file.txt';
        $filename = $this->publicImageDir . '/' .  $basename;

        $filesystem = $this->createMock(Filesystem::class);
        $filesystem
            ->expects($this->never())
            ->method('readFile');

        $mimeTypeGuesser = $this->createStub(MimeTypeGuesserInterface::class);

        $filePathReader = new FilePathReader(
            publicImageDir: $this->publicImageDir,
            filesystem: $filesystem,
            mimeTypeGuesser: $mimeTypeGuesser,
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(sprintf('File "%s" does not exist or is not readable.', $filename));

        $filePath = $this->createMock(FilePath::class);
        $filePath
            ->expects($this->once())
            ->method('getBasename')
            ->willReturn($basename);

        $filePathReader->read($filePath);
    }

    #[Test]
    public function throwsExceptionWhenTheMimeTypeCannotBeDetermined(): void
    {
        $basename = self::TEST_IMAGE_FILENAME;
        $filename = $this->publicImageDir . '/' . $basename;

        $filesystem = $this->createStub(Filesystem::class);

        $mimeTypeGuesser = $this->createMock(MimeTypeGuesserInterface::class);
        $mimeTypeGuesser
            ->expects($this->once())
            ->method('guessMimeType')
            ->with($filename)
            ->willReturn(null);

        $filePathReader = new FilePathReader(
            publicImageDir: $this->publicImageDir,
            filesystem: $filesystem,
            mimeTypeGuesser: $mimeTypeGuesser,
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(sprintf('Unable to determine the MIME type of "%s".', $filename));

        $filePath = $this->createMock(FilePath::class);
        $filePath
            ->expects($this->once())
            ->method('getBasename')
            ->willReturn($basename);

        $filePathReader->mimeType($filePath);
    }

    #[Test]
    public function readsFile(): void
    {
        $basename = self::TEST_IMAGE_FILENAME;
        $filename = $this->publicImageDir . '/' . $basename;

        $contents = 'file contents';

        $filesystem = $this->createMock(Filesystem::class);
        $filesystem
            ->expects($this->once())
            ->method('readFile')
            ->with($filename)
            ->willReturn($contents);

        $mimeTypeGuesser = $this->createMock(MimeTypeGuesserInterface::class);
        $mimeTypeGuesser
            ->expects($this->never())
            ->method('guessMimeType');

        $filePathReader = new FilePathReader(
            publicImageDir: $this->publicImageDir,
            filesystem: $filesystem,
            mimeTypeGuesser: $mimeTypeGuesser,
        );

        $filePath = $this->createMock(FilePath::class);
        $filePath
            ->expects($this->once())
            ->method('getBasename')
            ->willReturn($basename);

        $actual = $filePathReader->read($filePath);

        self::assertSame($contents, $actual);
    }

    #[Test]
    public function readsFileFromRealFile(): void
    {
        $basename = self::TEST_IMAGE_FILENAME;
        $filename = $this->publicImageDir . '/' . $basename;

        $filesystem = new Filesystem();

        $mimeTypeGuesser = $this->createMock(MimeTypeGuesserInterface::class);
        $mimeTypeGuesser
            ->expects($this->never())
            ->method('guessMimeType');

        $filePathReader = new FilePathReader(
            publicImageDir: $this->publicImageDir,
            filesystem: $filesystem,
            mimeTypeGuesser: $mimeTypeGuesser,
        );

        $filePath = $this->createMock(FilePath::class);
        $filePath
            ->expects($this->once())
            ->method('getBasename')
            ->willReturn($basename);

        $contents = $filePathReader->read($filePath);

        self::assertStringEqualsFile($filename, $contents);
    }

    #[Test]
    public function detectsTheMimeTypeOfThePhotographFile(): void
    {
        $basename = self::TEST_IMAGE_FILENAME;
        $filename = $this->publicImageDir . '/' . $basename;
        $mimeType = 'image/jpeg';

        $filesystem = $this->createMock(Filesystem::class);
        $filesystem
            ->expects($this->never())
            ->method('readFile');

        $mimeTypeGuesser = $this->createMock(MimeTypeGuesserInterface::class);
        $mimeTypeGuesser
            ->expects($this->once())
            ->method('guessMimeType')
            ->with($filename)
            ->willReturn($mimeType);

        $filePathReader = new FilePathReader(
            publicImageDir: $this->publicImageDir,
            filesystem: $filesystem,
            mimeTypeGuesser: $mimeTypeGuesser,
        );

        $filePath = $this->createMock(FilePath::class);
        $filePath
            ->expects($this->once())
            ->method('getBasename')
            ->willReturn(self::TEST_IMAGE_FILENAME);

        $mimeTypeFromFilePathReader = $filePathReader->mimeType($filePath);

        self::assertSame($mimeTypeFromFilePathReader, $mimeType);
    }
}
