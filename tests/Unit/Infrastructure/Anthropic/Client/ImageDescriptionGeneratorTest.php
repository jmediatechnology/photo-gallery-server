<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Anthropic\Client;

use Anthropic\Client;
use Anthropic\Messages\ContentBlock;
use Anthropic\Messages\Message;
use Anthropic\Services\MessagesService;
use App\Infrastructure\Anthropic\Client\ImageDescriptionGenerator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ImageDescriptionGeneratorTest extends TestCase
{
    #[Test]
    public function canDescribeBase64Image(): void
    {
        $description = 'Awesome description';

        $base64Image = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=';
        $mimeType = 'image/jpg';

        $contentBlock = $this->createStub(ContentBlock::class);
        $contentBlock->text = $description;

        $content = [
            $contentBlock
        ];

        $message = $this->createStub(Message::class);
        $message->content = $content;

        $messages = $this->createMock(MessagesService::class);
        $messages
            ->expects($this->once())
            ->method('create')
            ->with(
                maxTokens: 150,
                messages: [
                    [
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
                                'text' => ImageDescriptionGenerator::TEXT,
                            ],
                        ],
                    ]
                ],
                model: 'claude-haiku-4-5-20251001',
            )
            ->willReturn($message)
        ;

        $client = $this->createStub(Client::class);
        $client->messages = $messages;

        $actual = new ImageDescriptionGenerator(
            client: $client,
            model: 'claude-haiku-4-5-20251001',
            maxTokens: 150,
        )->describe(
            base64Image: $base64Image,
            mimeType: $mimeType
        );

        $this->assertEquals(expected: $description, actual: $actual);
    }
}
