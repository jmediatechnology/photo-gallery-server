<?php

namespace App\Infrastructure\ImageDescription\Factory;

use App\Domain\Service\ImageDescriptionGeneratorInterface;
use App\Infrastructure\ImageDescription\Enum\ImageDescriptionProvider;
use Symfony\Contracts\Service\ServiceProviderInterface;

class ImageDescriptionGeneratorFactory
{
    /**
     * @param ServiceProviderInterface<ImageDescriptionGeneratorInterface> $serviceProvider
     */
    public function __construct(
        private ServiceProviderInterface $serviceProvider,
        private ImageDescriptionProvider $provider,
    ) {}

    public function create(): ImageDescriptionGeneratorInterface
    {
        return $this->serviceProvider->get($this->provider->value);
    }
}
