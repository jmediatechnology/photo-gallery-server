<?php

namespace App\Tests\Unit\Infrastructure\Symfony\Ai;

use App\Domain\Entity\Photograph;
use App\Domain\Enum\ImageDescriptionGenerationFailureReason;
use App\Domain\Exception\ImageDescriptionGenerationFailureException;
use App\Domain\ValueObject\CreatedAt;
use App\Domain\ValueObject\FilePath;
use App\Domain\ValueObject\Title;
use App\Domain\ValueObject\UpdatedAt;
use App\Domain\ValueObject\UUID;
use App\Infrastructure\Symfony\Ai\ImageDescriptionGenerator;
use App\Infrastructure\Symfony\Filesystem\FilePathReaderInterface;
use App\Infrastructure\Text\SentenceTruncatorInterface;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\AI\Platform\Exception\AuthenticationException;
use Symfony\AI\Platform\Exception\ExceptionInterface;
use Symfony\AI\Platform\Exception\RateLimitExceededException;
use Symfony\AI\Platform\Exception\ServerException;
use Symfony\AI\Platform\Message\Content\Image;
use Symfony\AI\Platform\Message\Content\Text;
use Symfony\AI\Platform\Message\MessageBag;
use Symfony\AI\Platform\Message\UserMessage;
use Symfony\AI\Platform\Model;
use Symfony\AI\Platform\PlatformInterface;
use Symfony\AI\Platform\Result\DeferredResult;
use Symfony\AI\Platform\Result\InMemoryRawResult;
use Symfony\AI\Platform\ResultConverterInterface;
use Symfony\AI\Platform\Test\InMemoryPlatform;
use Throwable;

class ImageDescriptionGeneratorTest extends TestCase
{
    private const string MODEL = 'test-model';
    private const string IMAGE_BYTES = "\xFF\xD8\xFF\xE0 raw jpeg bytes";
    private const string MIME_TYPE = 'image/jpeg';
    private const string FILE_PATH = '/images/5c3f0e4e-8d2b-4b8e-9a51-2f6a7d1c9e01.jpg';

    /** Anthropic's limit: 10 MB, measured on the base64 payload. */
    private const int MAX_IMAGE_BYTES = 10_485_760;

    /** The largest raw file whose base64 encoding fits: 4 * ceil(7 864 320 / 3) = 10 485 760. */
    private const int LARGEST_RAW_IMAGE_THAT_FITS = 7_864_320;

    /**
     * @throws ExceptionInterface
     */
    #[Test]
    public function sendsPromptAndRawPhotographBytesToConfiguredModel(): void
    {
        $sentModelName = null;
        $sentInput = null;
        $sentOptions = null;
        $platform = new InMemoryPlatform(
            static function (Model $model, array|string|object $input, array $options) use (&$sentModelName, &$sentInput, &$sentOptions): string {
                $sentModelName = $model->getName();
                $sentInput = $input;
                $sentOptions = $options;

                return 'A red boat on a calm sea.';
            },
        );

        $generator = new ImageDescriptionGenerator(
            platform: $platform,
            model: self::MODEL,
            filePathReader: $this->createFilePathReader(),
            sentenceTruncator: $this->createPassThroughSentenceTruncator(),
            options: ['max_tokens' => 175],
        );

        $generator->describe($this->createPhotograph());

        self::assertSame(self::MODEL, $sentModelName);
        self::assertSame(['max_tokens' => 175], $sentOptions);
        self::assertInstanceOf(MessageBag::class, $sentInput);

        $messages = $sentInput->getMessages();
        self::assertCount(1, $messages);

        [$userMessage] = $messages;
        self::assertInstanceOf(UserMessage::class, $userMessage);

        [$prompt, $image] = $userMessage->getContent();
        self::assertInstanceOf(Text::class, $prompt);
        self::assertStringContainsString('description of this photo', $prompt->getText());
        self::assertInstanceOf(Image::class, $image);
        self::assertSame(self::MIME_TYPE, $image->getFormat());
        self::assertSame(self::IMAGE_BYTES, $image->asBinary());
    }

    /**
     * @throws ExceptionInterface
     */
    #[Test]
    public function returnsModelOutputTruncatedToTheDescriptionMaxLength(): void
    {
        $platform = new InMemoryPlatform('A red boat on a calm sea. The sun sets behind the hills.');

        $sentenceTruncator = $this->createMock(SentenceTruncatorInterface::class);
        $sentenceTruncator
            ->expects($this->once())
            ->method('truncate')
            ->with('A red boat on a calm sea. The sun sets behind the hills.', 512)
            ->willReturn('A red boat on a calm sea.');

        $generator = new ImageDescriptionGenerator(
            platform: $platform,
            model: self::MODEL,
            filePathReader: $this->createFilePathReader(),
            sentenceTruncator: $sentenceTruncator,
        );

        $description = $generator->describe($this->createPhotograph());

        self::assertSame('A red boat on a calm sea.', (string) $description);
    }

    /**
     * @throws ExceptionInterface
     */
    #[Test]
    public function sendsAnImageWhoseBase64PayloadIsExactlyAtTheLimit(): void
    {
        $generator = new ImageDescriptionGenerator(
            platform: new InMemoryPlatform('A red boat on a calm sea.'),
            model: self::MODEL,
            filePathReader: $this->createFilePathReader(str_repeat("\0", self::LARGEST_RAW_IMAGE_THAT_FITS)),
            sentenceTruncator: $this->createPassThroughSentenceTruncator(),
            maxImageBytes: self::MAX_IMAGE_BYTES,
        );

        self::assertSame('A red boat on a calm sea.', (string) $generator->describe($this->createPhotograph()));
    }

    #[Test]
    public function rejectsAnImageWhoseBase64PayloadExceedsTheLimitWithoutCallingTheProvider(): void
    {
        $platform = $this->createMock(PlatformInterface::class);
        $platform->expects($this->never())->method('invoke');

        $generator = new ImageDescriptionGenerator(
            platform: $platform,
            model: self::MODEL,
            filePathReader: $this->createFilePathReader(str_repeat("\0", self::LARGEST_RAW_IMAGE_THAT_FITS + 1)),
            sentenceTruncator: $this->createPassThroughSentenceTruncator(),
            maxImageBytes: self::MAX_IMAGE_BYTES,
        );

        $failure = $this->catchFailure(fn () => $generator->describe($this->createPhotograph()));

        self::assertSame(ImageDescriptionGenerationFailureReason::ImageTooLarge, $failure->reason);
    }

    #[Test]
    #[DataProvider('provideTransientProviderFailures')]
    public function translatesTransientProviderFailuresIntoADomainFailure(
        Throwable $providerFailure,
        ImageDescriptionGenerationFailureReason $expectedReason,
        ?int $expectedRetryAfter,
    ): void {
        $generator = new ImageDescriptionGenerator(
            platform: $this->createPlatformFailingWith($providerFailure),
            model: self::MODEL,
            filePathReader: $this->createFilePathReader(),
            sentenceTruncator: $this->createPassThroughSentenceTruncator(),
        );

        $failure = $this->catchFailure(fn () => $generator->describe($this->createPhotograph()));

        self::assertSame($expectedReason, $failure->reason);
        self::assertSame($expectedRetryAfter, $failure->retryAfter);
        self::assertSame($providerFailure, $failure->getPrevious());
    }

    /**
     * @throws ExceptionInterface
     */
    #[Test]
    public function letsUnexpectedProviderFailuresThroughUntranslated(): void
    {
        $generator = new ImageDescriptionGenerator(
            platform: $this->createPlatformFailingWith(new AuthenticationException('invalid x-api-key')),
            model: self::MODEL,
            filePathReader: $this->createFilePathReader(),
            sentenceTruncator: $this->createPassThroughSentenceTruncator(),
        );

        $this->expectException(AuthenticationException::class);

        $generator->describe($this->createPhotograph());
    }

    /**
     * @return iterable<string, array{Throwable, ImageDescriptionGenerationFailureReason, ?int}>
     */
    public static function provideTransientProviderFailures(): iterable
    {
        yield 'rate limited' => [new RateLimitExceededException(12), ImageDescriptionGenerationFailureReason::ProviderBusy, 12];
        yield 'overloaded' => [new ServerException(529), ImageDescriptionGenerationFailureReason::ProviderUnavailable, null];
    }

    /**
     * Like a real platform, the failure surfaces when the deferred result is read, not when invoke() returns.
     */
    private function createPlatformFailingWith(Throwable $providerFailure): PlatformInterface
    {
        $resultConverter = $this->createStub(ResultConverterInterface::class);
        $resultConverter->method('convert')->willThrowException($providerFailure);

        $platform = $this->createStub(PlatformInterface::class);
        $platform->method('invoke')->willReturn(new DeferredResult($resultConverter, new InMemoryRawResult()));

        return $platform;
    }

    /**
     * @param callable(): mixed $describe
     */
    private function catchFailure(callable $describe): ImageDescriptionGenerationFailureException
    {
        try {
            $describe();
        } catch (ImageDescriptionGenerationFailureException $failure) {
            return $failure;
        }

        self::fail(sprintf('Expected %s to be thrown.', ImageDescriptionGenerationFailureException::class));
    }

    private function createFilePathReader(string $bytes = self::IMAGE_BYTES): FilePathReaderInterface
    {
        $readsPhotographFile = static fn (FilePath $filePath): bool => (string) $filePath === self::FILE_PATH;

        $filePathReader = $this->createStub(FilePathReaderInterface::class);
        $filePathReader
            ->method('read')
            ->willReturnCallback(
                static fn (FilePath $filePath): string => $readsPhotographFile($filePath) ? $bytes : '',
            );

        $filePathReader
            ->method('mimeType')
            ->willReturnCallback(
                static fn (FilePath $filePath): string => $readsPhotographFile($filePath) ? self::MIME_TYPE : '',
            );

        return $filePathReader;
    }

    private function createPassThroughSentenceTruncator(): SentenceTruncatorInterface
    {
        $sentenceTruncator = $this->createStub(SentenceTruncatorInterface::class);
        $sentenceTruncator
            ->method('truncate')
            ->willReturnArgument(0);

        return $sentenceTruncator;
    }

    private function createPhotograph(): Photograph
    {
        return new Photograph(
            uuid: new UUID('5c3f0e4e-8d2b-4b8e-9a51-2f6a7d1c9e01'),
            title: new Title('Sunset'),
            description: null,
            filePath: new FilePath(self::FILE_PATH),
            createdAt: new CreatedAt(new DateTimeImmutable()),
            updatedAt: new UpdatedAt(new DateTimeImmutable()),
        );
    }
}
