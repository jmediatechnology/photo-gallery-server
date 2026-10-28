<?php

namespace App\Tests\Unit\Infrastructure\ImageDescription\Factory;

use App\Domain\Service\ImageDescriptionGeneratorInterface;
use App\Infrastructure\ImageDescription\Enum\ImageDescriptionProvider;
use App\Infrastructure\ImageDescription\Factory\ImageDescriptionGeneratorFactory;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Service\ServiceProviderInterface;

class ImageDescriptionGeneratorFactoryTest extends TestCase
{
    #[Test]
    public function canCreateImageDescriptionGenerator(): void
    {
        $provider = ImageDescriptionProvider::Ollama;

        $imageDescriptionGenerator = $this->createStub(ImageDescriptionGeneratorInterface::class);

        $serviceProvider = $this->createMock(ServiceProviderInterface::class);
        $serviceProvider
            ->expects($this->once())
            ->method('get')
            ->with($provider->value)
            ->willReturn($imageDescriptionGenerator);

        $factory = new ImageDescriptionGeneratorFactory(
            $serviceProvider,
            $provider
        );
        $actual = $factory->create();

        self::assertSame($imageDescriptionGenerator, $actual);
    }
}
