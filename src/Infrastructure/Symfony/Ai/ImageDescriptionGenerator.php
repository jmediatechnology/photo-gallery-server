<?php

namespace App\Infrastructure\Symfony\Ai;

use App\Domain\Entity\Photograph;
use App\Domain\Service\ImageDescriptionGeneratorInterface;
use App\Domain\ValueObject\Description;
use App\Infrastructure\Symfony\Filesystem\FilePathReader;
use App\Infrastructure\Text\SentenceTruncatorInterface;
use Symfony\AI\Platform\Exception\ExceptionInterface;
use Symfony\AI\Platform\Message\Content\Image;
use Symfony\AI\Platform\Message\Message;
use Symfony\AI\Platform\Message\MessageBag;
use Symfony\AI\Platform\PlatformInterface;

class ImageDescriptionGenerator implements ImageDescriptionGeneratorInterface
{
    private const int LENGTH = 512;

    private const string PROMPT = 'Write a short description of this photo between 30 and 60 words in one or two sentences, min 150 chars, max 512 chars. Output only the description, nothing else.';

    public function __construct(
        private PlatformInterface $platform,
        private string            $model,
        private FilePathReader    $filePathReader,
        private SentenceTruncatorInterface $sentenceTruncator,
        private array $options = [],
    ) {}

    /**
     * @throws ExceptionInterface
     */
    public function describe(Photograph $photograph): Description
    {
        $filePath = $photograph->filePath();
        $base64Image = $this->filePathReader->read($filePath);
        $mimeType = $this->filePathReader->mimeType($filePath);

        $image = Image::fromDataUrl(sprintf('data:%s;base64,%s', $mimeType, $base64Image));
        $userMessage = Message::ofUser(self::PROMPT, $image);
        $messageBag = new MessageBag($userMessage);

        $deferredResult = $this->platform->invoke(
            model: $this->model,
            input: $messageBag,
            options: $this->options,
        );
        $results = $deferredResult->asText();

        $description = $this->sentenceTruncator->truncate(
            text: $results,
            maxLength: self::LENGTH
        );
        return new Description($description);
    }
}
