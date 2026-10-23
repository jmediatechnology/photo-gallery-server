<?php

namespace App\Infrastructure\ImageDescription\Factory;

use App\Domain\Service\ImageDescriptionGeneratorInterface;
use App\Infrastructure\ImageDescription\Enum\ImageDescriptionProvider;
use Symfony\Contracts\Service\ServiceProviderInterface;

class ImageDescriptionGeneratorFactory
{
    /**
     * @param ServiceProviderInterface<ImageDescriptionGeneratorInterface> $generators
     */
    public function __construct(
        private ServiceProviderInterface $generators,
        private ImageDescriptionProvider $provider,
    ) {}

    public function create(): ImageDescriptionGeneratorInterface
    {
        return $this->generators->get($this->provider->value);
    }
}
