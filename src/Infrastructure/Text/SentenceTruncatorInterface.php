<?php

namespace App\Infrastructure\Text;

interface SentenceTruncatorInterface
{
    public function truncate(string $text, int $maxLength): string;
}
