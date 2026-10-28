<?php

namespace App\Application\Handler\Photograph;

use App\Application\Query\Photograph\GenerateDescriptionForKnownPhotographUuidQuery;
use App\Domain\Entity\Photograph;
use App\Domain\Repository\PhotographRepositoryInterface;
use App\Domain\Service\ImageDescriptionGeneratorInterface;
use App\Domain\ValueObject\UUID;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use UnexpectedValueException;

#[AsMessageHandler]
class GenerateDescriptionHandler
{
    public function __construct(
        private PhotographRepositoryInterface $photographRepository,
        private ImageDescriptionGeneratorInterface $imageDescriptionGenerator,
    ) {}

    public function __invoke(GenerateDescriptionForKnownPhotographUuidQuery $query): string
    {
        $photograph = $this->photographRepository->findOneByUUID(new UUID($query->uuid()));

        if (!$photograph instanceof Photograph) {
            throw new UnexpectedValueException('Photograph not found');
        }

        return $this->imageDescriptionGenerator->describe($photograph)->__toString();
    }
}
