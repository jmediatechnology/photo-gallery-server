<?php

namespace App\Infrastructure\Symfony\Ai;

use App\Domain\Entity\Photograph;
use App\Domain\Enum\ImageDescriptionGenerationFailureReason;
use App\Domain\Exception\ImageDescriptionGenerationFailureException;
use App\Domain\Service\ImageDescriptionGeneratorInterface;
use App\Domain\ValueObject\Description;
use App\Infrastructure\Symfony\Filesystem\FilePathReaderInterface;
use App\Infrastructure\Text\SentenceTruncatorInterface;
use Symfony\AI\Platform\Exception\ExceptionInterface;
use Symfony\AI\Platform\Exception\RateLimitExceededException;
use Symfony\AI\Platform\Exception\ServerException;
use Symfony\AI\Platform\Message\Content\Image;
use Symfony\AI\Platform\Message\Message;
use Symfony\AI\Platform\Message\MessageBag;
use Symfony\AI\Platform\PlatformInterface;

class ImageDescriptionGenerator implements ImageDescriptionGeneratorInterface
{
    private const int MAX_DESCRIPTION_LENGTH = 512;

    private const string PROMPT = 'Write a short description of this photo between 30 and 60 words in one or two sentences, min 150 chars, max 512 chars. Output only the description, nothing else.';

    /**
     * @param array<string, mixed> $options       passed verbatim to the provider API, e.g. ['max_tokens' => 175]
     * @param int|null             $maxImageBytes the provider's per-image limit, measured on the base64 payload; null = no limit
     */
    public function __construct(
        private PlatformInterface $platform,
        private string $model,
        private FilePathReaderInterface $filePathReader,
        private SentenceTruncatorInterface $sentenceTruncator,
        private array $options = [],
        private ?int $maxImageBytes = null,
    ) {}

    /**
     * @throws ImageDescriptionGenerationFailureException for expected failures (too large, provider busy or down)
     * @throws ExceptionInterface                         for unexpected provider failures (bad key, unknown model, ...)
     */
    public function describe(Photograph $photograph): Description
    {
        $filePath = $photograph->filePath();

        $bytes = $this->filePathReader->read($filePath);
        if ($this->exceedsImageLimit($bytes)) {
            throw new ImageDescriptionGenerationFailureException(ImageDescriptionGenerationFailureReason::ImageTooLarge);
        }

        $image = new Image($bytes, $this->filePathReader->mimeType($filePath));
        $messageBag = new MessageBag(Message::ofUser(self::PROMPT, $image));

        $deferredResult = $this->platform->invoke(
            model: $this->model,
            input: $messageBag,
            options: $this->options,
        );

        try {
            $text = $deferredResult->asText();
        } catch (RateLimitExceededException $exception) {
            throw new ImageDescriptionGenerationFailureException(
                reason: ImageDescriptionGenerationFailureReason::ProviderBusy,
                retryAfter: $exception->getRetryAfter(),
                previous: $exception,
            );
        } catch (ServerException $exception) {
            throw new ImageDescriptionGenerationFailureException(
                reason: ImageDescriptionGenerationFailureReason::ProviderUnavailable,
                previous: $exception,
            );
        }

        $description = $this->sentenceTruncator->truncate(
            text: $text,
            maxLength: self::MAX_DESCRIPTION_LENGTH
        );

        return new Description($description);
    }

    /**
     * The limit applies to the base64 payload the bridge sends, not the raw file size.
     * Base64 turns every 3-byte group into 4 chars, padding the last group.
     * So the encoded length is 4 * ceil(n / 3).
     *
     * Computed arithmetically to avoid allocating the encoded string just to measure it.
     */
    private function exceedsImageLimit(string $bytes): bool
    {
        return $this->maxImageBytes !== null && 4 * intdiv(strlen($bytes) + 2, 3) > $this->maxImageBytes;
    }
}
