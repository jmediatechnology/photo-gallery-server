<?php

declare(strict_types=1);

namespace App\Tests\Application\Photograph;

use App\Domain\Entity\photograph;
use App\Domain\Enum\ImageDescriptionGenerationFailureReason;
use App\Domain\Exception\ImageDescriptionGenerationFailureException;
use App\Domain\Service\ImageDescriptionGeneratorInterface;
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
    private const string PHOTOGRAPH_UUID = 'ee4b30de-710a-4551-b3eb-f640154ef495';

    /**
     * phpunit.dist.xml forces IMAGE_DESCRIPTION_PROVIDER=fake, so this is the generator the handler gets.
     */
    private const string FAKE_GENERATOR_SERVICE_ID = 'app.image_description_generator.fake';

    protected function setUp(): void
    {
        parent::setUp();

        self::copyTestFileToPublicFolder();
    }

    #[Test]
    public function canGenerateImageDescriptionForKnownPhotograph(): void
    {
        $this->savePhotograph();

        $json = $this->jsonRequest(
            method: 'POST',
            uri: '/photographs/' . self::PHOTOGRAPH_UUID . '/generate-description',
        );

        self::assertArrayHasKey('description', $json);
        self::assertImageDescription($json['description']);

        self::assertResponseIsSuccessful();
    }

    #[Test]
    public function returnsServiceUnavailableWhenTheImageDescriptionCouldNotBeGenerated(): void
    {
        $this->replaceGeneratorWithOneThatFailsWith(ImageDescriptionGenerationFailureReason::ProviderBusy);
        $this->savePhotograph();

        $json = $this->jsonRequest(
            method: 'POST',
            uri: '/photographs/' . self::PHOTOGRAPH_UUID . '/generate-description',
        );

        self::assertResponseStatusCodeSame(503);
        self::assertResponseHeaderSame('Retry-After', '30');
        self::assertSame(['errors' => 'Description service unavailable, try again later.'], $json);
    }

    #[Test]
    public function returnsUnprocessableEntityWhenThePhotographIsTooLargeToDescribe(): void
    {
        $this->replaceGeneratorWithOneThatFailsWith(ImageDescriptionGenerationFailureReason::ImageTooLarge);
        $this->savePhotograph();

        $json = $this->jsonRequest(
            method: 'POST',
            uri: '/photographs/' . self::PHOTOGRAPH_UUID . '/generate-description',
        );

        self::assertResponseStatusCodeSame(422);
        self::assertResponseNotHasHeader('Retry-After');
        self::assertSame(['errors' => 'This photograph is too large to generate a description for.'], $json);
    }

    /**
     * Runs the real bus, middleware and exception listener; only the generator fails.
     * Must happen before the first request, while the generator is not yet created.
     */
    private function replaceGeneratorWithOneThatFailsWith(ImageDescriptionGenerationFailureReason $reason): void
    {
        $generator = $this->createMock(ImageDescriptionGeneratorInterface::class);
        $generator
            ->expects($this->once())
            ->method('describe')
            ->willThrowException(new ImageDescriptionGenerationFailureException($reason));

        static::getContainer()->set(self::FAKE_GENERATOR_SERVICE_ID, $generator);
    }

    private function savePhotograph(): void
    {
        $this->photographRepository->save(new Photograph(
            uuid: new UUID(self::PHOTOGRAPH_UUID),
            title: new Title('Solina Bieszczady Poland'),
            description: null,
            filePath: new FilePath('/images/' . self::TEST_FILE_NAME),
            createdAt: new CreatedAt(new DateTimeImmutable()),
            updatedAt: new UpdatedAt(new DateTimeImmutable()),
        ));
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
