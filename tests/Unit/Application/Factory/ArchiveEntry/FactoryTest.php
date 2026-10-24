<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Factory\ArchiveEntry;

use App\Application\Factory\ArchiveEntry\Factory;
use App\Application\ValueObject\ArchiveEntry;
use App\Domain\Entity\Photograph;
use App\Domain\ValueObject\CreatedAt;
use App\Domain\ValueObject\FilePath;
use App\Domain\ValueObject\Title;
use App\Domain\ValueObject\UpdatedAt;
use App\Domain\ValueObject\UUID;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Filesystem\Filesystem;

final class FactoryTest extends TestCase
{
    private string $publicImageDir;

    protected function setUp(): void
    {
        $this->publicImageDir = sys_get_temp_dir() . '/archive-entry-factory-test-' . uniqid('', true);
        new Filesystem()->mkdir($this->publicImageDir);
    }

    protected function tearDown(): void
    {
        new Filesystem()->remove($this->publicImageDir);
    }

    #[Test]
    public function createsAnEntryNamedAfterTheTitleWithPathAndSize(): void
    {
        $photograph = $this->createPhotographWithFile('Sunset', 'sunset-file.jpg', contents: '12345');

        $entries = new Factory($this->publicImageDir)->createForPhotographs([$photograph]);

        $expected = [new ArchiveEntry(name: 'Sunset.jpg', path: $this->publicImageDir . '/sunset-file.jpg', size: 5)];
        self::assertEquals($expected, $entries);
    }

    #[Test]
    #[DataProvider('provideUnsafeTitles')]
    public function replacesCharactersThatAreUnsafeInFileNames(string $title, string $expectedName): void
    {
        $photograph = $this->createPhotographWithFile($title, 'file.jpg');

        [$entry] = new Factory($this->publicImageDir)->createForPhotographs([$photograph]);

        self::assertSame($expectedName, $entry->name());
    }

    #[Test]
    public function fallsBackToTheFileNameWhenNothingIsLeftOfTheTitle(): void
    {
        $photograph = $this->createPhotographWithFile('...', '5c3f0e4e-8d2b-4b8e-9a51-2f6a7d1c9e01.jpg');

        [$entry] = new Factory($this->publicImageDir)->createForPhotographs([$photograph]);

        self::assertSame('5c3f0e4e-8d2b-4b8e-9a51-2f6a7d1c9e01.jpg', $entry->name());
    }

    #[Test]
    public function leavesTheExtensionOutWhenTheFileHasNone(): void
    {
        $photograph = $this->createPhotographWithFile('Sunset', 'sunset-file');

        [$entry] = new Factory($this->publicImageDir)->createForPhotographs([$photograph]);

        self::assertSame('Sunset', $entry->name());
    }

    #[Test]
    public function numbersNamesThatWouldCollideIgnoringCase(): void
    {
        $photographs = [
            $this->createPhotographWithFile('Sunset', 'a.jpg'),
            $this->createPhotographWithFile('sunset', 'b.jpg'),
            $this->createPhotographWithFile('Sun/set', 'c.jpg'),
            $this->createPhotographWithFile('Sun_set', 'd.jpg'),
        ];

        $entries = new Factory($this->publicImageDir)->createForPhotographs($photographs);

        self::assertSame(
            ['Sunset.jpg', 'sunset (2).jpg', 'Sun_set.jpg', 'Sun_set (2).jpg'],
            array_map(static fn (ArchiveEntry $entry): string => $entry->name(), $entries),
        );
    }

    #[Test]
    public function failsWhenThePhotographFileIsMissing(): void
    {
        $photograph = $this->createPhotograph('Sunset', 'missing.jpg');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(sprintf('Photograph file "%s/missing.jpg" is missing or not readable.', $this->publicImageDir));

        new Factory($this->publicImageDir)->createForPhotographs([$photograph]);
    }

    private function createPhotographWithFile(string $title, string $fileName, string $contents = ''): Photograph
    {
        new Filesystem()->dumpFile($this->publicImageDir . '/' . $fileName, $contents);

        return $this->createPhotograph($title, $fileName);
    }

    private function createPhotograph(string $title, string $fileName): Photograph
    {
        return new Photograph(
            uuid: new UUID('5c3f0e4e-8d2b-4b8e-9a51-2f6a7d1c9e01'),
            title: new Title($title),
            description: null,
            filePath: new FilePath('/images/' . $fileName),
            createdAt: new CreatedAt(new DateTimeImmutable()),
            updatedAt: new UpdatedAt(new DateTimeImmutable()),
        );
    }


    /**
     * @return array<string, array{string, string}>
     */
    public static function provideUnsafeTitles(): array
    {
        return [
            'path traversal' => ['../../etc/passwd', '_.._etc_passwd.jpg'],
            'backslashes' => ['C:\\Windows\\System32', 'C__Windows_System32.jpg'],
            'characters Windows forbids' => ['What? *Really* <yes> "no" a|b', 'What_ _Really_ _yes_ _no_ a_b.jpg'],
            'control characters' => ["Line\nbreak\tand\x00null", 'Line_break_and_null.jpg'],
            'leading dot and trailing dots and spaces' => ['.hidden title. . ', 'hidden title.jpg'],
            'non-ASCII is kept' => ['Zakopane, Tatry – zima ❄', 'Zakopane, Tatry – zima ❄.jpg'],
        ];
    }
}
