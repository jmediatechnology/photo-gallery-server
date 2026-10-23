<?php

namespace App\Tests\Integration\Infrastructure\ImageDescription;

use App\Domain\Service\ImageDescriptionGeneratorInterface;
use App\Infrastructure\ImageDescription\FakeImageDescription\FakeImageDescriptionGenerator;
use App\Infrastructure\Symfony\Ai\ImageDescriptionGenerator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ImageDescriptionGeneratorWiringTest extends KernelTestCase
{
    private const string PROVIDER_ENV = 'IMAGE_DESCRIPTION_PROVIDER';

    private string|null $originalEnv;
    private string|null $originalServer;

    protected function setUp(): void
    {
        $this->originalEnv = $_ENV[self::PROVIDER_ENV] ?? null;
        $this->originalServer = $_SERVER[self::PROVIDER_ENV] ?? null;
    }

    protected function tearDown(): void
    {
        $this->restore($_ENV, $this->originalEnv);
        $this->restore($_SERVER, $this->originalServer);

        parent::tearDown();
    }

    #[Test]
    #[DataProvider('provideProviders')]
    public function resolvesGeneratorForProvider(string $provider, string $expectedServiceId, string $expectedClass): void
    {
        $this->bootKernelWithProvider($provider);
        $container = static::getContainer();

        $generator = $container->get(ImageDescriptionGeneratorInterface::class);

        $this->assertInstanceOf($expectedClass, $generator);
        $this->assertSame($container->get($expectedServiceId), $generator);
    }

    /**
     * @return iterable<string, array{string, string, class-string}>
     */
    public static function provideProviders(): iterable
    {
        yield 'fake' => ['fake', 'app.image_description_generator.fake', FakeImageDescriptionGenerator::class];
        yield 'anthropic' => ['anthropic', 'app.image_description_generator.anthropic', ImageDescriptionGenerator::class];
        yield 'ollama' => ['ollama', 'app.image_description_generator.ollama', ImageDescriptionGenerator::class];
    }

    #[Test]
    public function failsLoudlyOnUnknownProvider(): void
    {
        $this->bootKernelWithProvider('olama');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Enum value "olama" is not backed by');

        static::getContainer()->get(ImageDescriptionGeneratorInterface::class);
    }

    private function bootKernelWithProvider(string $provider): void
    {
        $_ENV[self::PROVIDER_ENV] = $provider;
        $_SERVER[self::PROVIDER_ENV] = $provider;

        self::bootKernel();
    }

    /**
     * @param array<string, mixed> $variables
     */
    private function restore(array &$variables, string|null $originalValue): void
    {
        if ($originalValue === null) {
            unset($variables[self::PROVIDER_ENV]);

            return;
        }

        $variables[self::PROVIDER_ENV] = $originalValue;
    }
}
