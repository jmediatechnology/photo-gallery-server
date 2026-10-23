<?php

namespace App\Tests\Unit\Infrastructure\Symfony\Ai;

use App\Domain\Entity\Photograph;
use App\Domain\ValueObject\CreatedAt;
use App\Domain\ValueObject\FilePath;
use App\Domain\ValueObject\Title;
use App\Domain\ValueObject\UpdatedAt;
use App\Domain\ValueObject\UUID;
use App\Infrastructure\Symfony\Ai\ImageDescriptionGenerator;
use App\Infrastructure\Symfony\Filesystem\FilePathReader;
use App\Infrastructure\Text\SentenceTruncatorInterface;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\AI\Platform\Exception\ExceptionInterface;
use Symfony\AI\Platform\Message\Content\Image;
use Symfony\AI\Platform\Message\Content\Text;
use Symfony\AI\Platform\Message\MessageBag;
use Symfony\AI\Platform\Message\UserMessage;
use Symfony\AI\Platform\Model;
use Symfony\AI\Platform\Test\InMemoryPlatform;

class ImageDescriptionGeneratorTest extends TestCase
{
    private const string MODEL = 'test-model';
    private const string IMAGE_BYTES = 'image-bytes';
    private const string MIME_TYPE = 'image/jpeg';

    /**
     * @throws ExceptionInterface
     */
    #[Test]
    public function sendsPromptAndPhotographImageToConfiguredModel(): void
    {
        $sentModelName = null;
        $sentInput = null;
        $platform = new InMemoryPlatform(
            static function (Model $model, array|string|object $input) use (&$sentModelName, &$sentInput): string {
                $sentModelName = $model->getName();
                $sentInput = $input;

                return 'A red boat on a calm sea.';
            },
        );

        $generator = new ImageDescriptionGenerator(
            platform: $platform,
            model: self::MODEL,
            filePathReader: $this->createFilePathReader(),
            sentenceTruncator: $this->createPassThroughSentenceTruncator(),
        );

        $generator->describe($this->createPhotograph());

        self::assertSame(self::MODEL, $sentModelName);
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

    private function createFilePathReader(): FilePathReader
    {
        $filePathReader = $this->createStub(FilePathReader::class);
        $filePathReader->method('read')->willReturn(base64_encode(self::IMAGE_BYTES));
        $filePathReader->method('mimeType')->willReturn(self::MIME_TYPE);

        return $filePathReader;
    }

    private function createPassThroughSentenceTruncator(): SentenceTruncatorInterface
    {
        $sentenceTruncator = $this->createStub(SentenceTruncatorInterface::class);
        $sentenceTruncator->method('truncate')->willReturnArgument(0);

        return $sentenceTruncator;
    }

    private function createPhotograph(): Photograph
    {
        return new Photograph(
            uuid: new UUID('5c3f0e4e-8d2b-4b8e-9a51-2f6a7d1c9e01'),
            title: new Title('Sunset'),
            description: null,
            filePath: new FilePath('/images/5c3f0e4e-8d2b-4b8e-9a51-2f6a7d1c9e01.jpg'),
            createdAt: new CreatedAt(new DateTimeImmutable()),
            updatedAt: new UpdatedAt(new DateTimeImmutable()),
        );
    }
}
