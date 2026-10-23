<?php

namespace App\Infrastructure\Text;

use InvalidArgumentException;

class SentenceTruncator implements SentenceTruncatorInterface
{
    private const string SENTENCE_END = '/[.!?…]+["\'”’)\]]*(?=\s|$)/u';


    public function truncate(string $text, int $maxLength): string
    {
        $text = trim($text);

        if (strlen($text) < $maxLength) {
            return $text;
        }

        if ($maxLength < 1) {
            throw new InvalidArgumentException(sprintf('Max length must be at least 1, %d given.', $maxLength));
        }

        $truncatedStringLength = $this->findLastSentenceEndingOffsetForGivenMaxLength($text, $maxLength);
        if ($truncatedStringLength === null) {
            $truncatedStringLength = $this->findLastWordBoundary($text, $maxLength);
        }

        return substr($text, 0, $truncatedStringLength);
    }

    private function findLastSentenceEndingOffsetForGivenMaxLength(string $text, int $maxLength): ?int
    {
        $finalEndingOffset = null;

        /** @var array{0: array<array{string, int}>} $matches */
        $count = preg_match_all(self::SENTENCE_END, $text, $matches, PREG_OFFSET_CAPTURE);

        if (!$count) {
            return null;
        }

        /** @var array<array{0: string, 1: int}> $sentenceEndings */
        [$sentenceEndings] = $matches;

        /**
         * @var $ending string
         * @var $endingOffset int
         */
        foreach ($sentenceEndings as [$ending, $endingOffset]) {
            $endingLength = strlen($ending);
            $sentenceEndOffset = $endingOffset + $endingLength;

            $textShortened = substr(string: $text, offset: 0, length: $sentenceEndOffset);
            $textShortenedLength = mb_strlen($textShortened);

            if ($textShortenedLength > $maxLength) {
                break;
            }

            $finalEndingOffset = $sentenceEndOffset;
        }

        return $finalEndingOffset;
    }

    private function findLastWordBoundary(string $text, int $maxLength): ?int
    {
        $cutOffset = null;

        // match all by whitespace
        preg_match_all('/\s/', $text, $matches, PREG_OFFSET_CAPTURE);

        [$whitespaces] = $matches;

        /**
         * @var $whitespace string
         * @var $whitespaceOffset int
         */
        foreach ($whitespaces as [$whitespace, $whitespaceOffset]) {

            $cutOffset = $whitespaceOffset;

            if ($whitespaceOffset > $maxLength) {
                break;
            }
        }

        return $cutOffset;
    }
}
