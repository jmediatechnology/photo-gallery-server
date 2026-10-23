<?php

namespace App\Tests\Unit\Infrastructure\Text;

use App\Infrastructure\Text\SentenceTruncator;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class SentenceTruncatorTest extends TestCase
{
    private SentenceTruncator $sentenceTruncator;

    protected function setUp(): void
    {
        $this->sentenceTruncator = new SentenceTruncator();
    }

    #[Test]
    #[DataProvider('provideTexts')]
    public function truncatesToCompleteSentences(string $text, int $maxLength, string $expected): void
    {
        $actual = $this->sentenceTruncator->truncate($text, $maxLength);

        $this->assertSame($expected, $actual);
    }

    #[Test]
    public function throwsOnMaxLengthBelowOne(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Max length must be at least 1, 0 given.');

        $this->sentenceTruncator->truncate('A red boat.', 0);
    }

    /**
     * @return iterable<string, array{string, int, string}>
     */
    public static function provideTexts(): iterable
    {
        yield 'text that fits is returned unchanged' => [
            'A red boat. A calm sea.', 100, 'A red boat. A calm sea.',
        ];

        yield 'falls back to word boundary when first sentence does not fit' => [
            'I am Peter and I sail a red boat.', 8, 'I am Peter',
        ];

        yield 'text with no punctuation trims to first word boundary after max length' => [
            'Hello world this is a test string', 5, 'Hello world',
        ];

        yield 'keeps as many complete sentences as fit' => [
            'A red boat. A calm sea. A long sentence that does not fit anymore.', 30, 'A red boat. A calm sea.',
        ];

        yield 'cut exactly at the max length' => [
            'A red boat. A calm sea.', 23, 'A red boat. A calm sea.',
        ];

        yield 'trims surrounding whitespace' => [
            "  A red boat.\n", 100, 'A red boat.',
        ];

        yield 'question and exclamation marks end a sentence' => [
            'Is it a boat? Yes! It floats.', 20, 'Is it a boat? Yes!',
        ];

        yield 'ellipsis ends a sentence' => [
            'The boat drifts away… Nobody notices.', 25, 'The boat drifts away…',
        ];

        yield 'closing quote belongs to the sentence' => [
            'He said "a red boat." Then he left.', 25, 'He said "a red boat."',
        ];

        yield 'decimal point does not end a sentence' => [
            'The boat is 3.5 meters long. It is red.', 30, 'The boat is 3.5 meters long.',
        ];

        yield 'length is counted in characters, not bytes' => [
            'Één rode boot. Een kalme zee.', 14, 'Één rode boot.',
        ];
    }
}
