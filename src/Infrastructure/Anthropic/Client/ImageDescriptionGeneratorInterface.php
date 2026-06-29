<?php

namespace App\Infrastructure\Anthropic\Client;

interface ImageDescriptionGeneratorInterface
{
    public const string TEXT = 'Write a short description of this photo between 30 and 60 words in one or two sentences, min 150 chars, max 512 chars. Output only the description, nothing else.';

    public function describe(string $base64Image, string $mimeType): string;
}
