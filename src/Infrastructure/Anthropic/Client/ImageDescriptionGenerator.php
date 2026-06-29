<?php

namespace App\Infrastructure\Anthropic\Client;

use Anthropic\Client;
use Anthropic\Core\Exceptions\APIException;

class ImageDescriptionGenerator implements ImageDescriptionGeneratorInterface
{
    public const string TEXT = 'Write a short description of this photo between 30 and 60 words in one or two sentences, min 150 chars, max 512 chars. Output only the description, nothing else.';

    public function __construct(
        private Client $client,
        private string $model,
        private int $maxTokens,
    ) {}

    /**
     * @throws APIException
     */
    public function describe(string $base64Image, string $mimeType): string
    {
        $message = $this->client->messages->create(
            maxTokens: $this->maxTokens,
            messages: [[
                'role' => 'user',
                'content' => [
                    [
                        'type' => 'image',
                        'source' => [
                            'type' => 'base64',
                            'media_type' => $mimeType,
                            'data' => $base64Image,
                        ],
                    ],
                    [
                        'type' => 'text',
                        'text' => self::TEXT,
                    ],
                ],
            ]],
            model: $this->model,
        );

        return $message->content[0]->text;
    }
}
