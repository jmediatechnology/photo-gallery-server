<?php

declare(strict_types=1);

namespace App\Tests\Application\Photograph;

use App\Domain\Entity\photograph;
use App\Domain\ValueObject\CreatedAt;
use App\Domain\ValueObject\FilePath;
use App\Domain\ValueObject\Title;
use App\Domain\ValueObject\UpdatedAt;
use App\Domain\ValueObject\UUID;
use App\Tests\Application\ApiTestCase;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Filesystem;

class GenerateImageDescriptionActionTest extends ApiTestCase
{
    private const string TEST_FILE_NAME = 'ee4b30de-710a-4551-b3eb-f640154ef495.jpg';

    protected function setUp(): void
    {
        parent::setUp();

        self::copyTestFileToPublicFolder();
    }

    #[Test]
    public function canGenerateImageDescriptionForKnownPhotograph(): void
    {
        $photograph = new Photograph(
            uuid: new UUID('ee4b30de-710a-4551-b3eb-f640154ef495'),
            title: new Title('Solina Bieszczady Poland'),
            description: null,
            filePath: new FilePath('/images/ee4b30de-710a-4551-b3eb-f640154ef495.jpg'),
            createdAt: new CreatedAt(new DateTimeImmutable()),
            updatedAt: new UpdatedAt(new DateTimeImmutable()),
        );

        $this->photographRepository->save($photograph);

        $json = $this->jsonRequest(
            method: 'POST',
            uri: '/photographs/ee4b30de-710a-4551-b3eb-f640154ef495/generate-description',
        );

        self::assertArrayHasKey('description', $json);
        self::assertImageDescription($json['description']);

        self::assertResponseIsSuccessful();
    }

    private static function assertImageDescription(string $description): void
    {
        $descriptionLength = strlen($description);
        self::assertNotEmpty($description);
        self::assertGreaterThanOrEqual(150, $descriptionLength);
        self::assertLessThanOrEqual(512, $descriptionLength);

        $words = preg_split('/\s+/', trim($description));

        self::assertGreaterThan(30, count($words));
        self::assertStringEndsWith('.', trim($description));
    }

    private static function getFullPathToTestFile(): string
    {
        $fixtureImagesDir = self::getProjectDir() . '/fixtures/images/';
        return $fixtureImagesDir . self::TEST_FILE_NAME;
    }

    private static function getFullPathToPublicTestFile(): string
    {
        return self::$publicImagesDir . '/' . self::TEST_FILE_NAME;
    }

    private static function copyTestFileToPublicFolder(): void
    {
        $fullPathToTestFile = self::getFullPathToTestFile();
        $fullPathToTemporaryTestFile = self::getFullPathToPublicTestFile();

        if (!file_exists($fullPathToTestFile)) {
            throw new FileNotFoundException(sprintf('Error: unable to find "%s"', $fullPathToTestFile));
        }

        $filesystem = new Filesystem();
        $filesystem->copy(
            originFile: $fullPathToTestFile,
            targetFile: $fullPathToTemporaryTestFile,
        );

        if (!file_exists($fullPathToTemporaryTestFile)) {
            throw new FileNotFoundException(sprintf('Error: unable to find "%s"', $fullPathToTemporaryTestFile));
        }
    }
}
