<?php

namespace App\Tests\Unit\Application\Handlers\Photograph;

use App\Application\Handler\Photograph\GenerateDescriptionHandler;
use App\Application\Query\Photograph\GenerateDescriptionForKnownPhotographUuidQuery;
use App\Domain\Repository\PhotographRepositoryInterface;
use App\Domain\Service\ImageDescriptionGeneratorInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

final class GenerateDescriptionHandlerTest extends TestCase
{
    #[Test]
    public function triggersUnexpectedValueExceptionWhenPhotographRepositoryCantFindPhotograph(): void
    {
        $this->expectException(UnexpectedValueException::class);

        $photographRepository = $this->createMock(PhotographRepositoryInterface::class);
        $photographRepository
            ->expects($this->once())
            ->method('findOneByUUID')
            ->willReturn(null);

        $imageDescriptionGenerator = $this->createStub(ImageDescriptionGeneratorInterface::class);

        $query = $this->createMock(GenerateDescriptionForKnownPhotographUuidQuery::class);
        $query
            ->expects($this->once())
            ->method('uuid')
            ->willReturn('32c93f53-6ee8-40b8-a45c-0c11c1169d59');

        new GenerateDescriptionHandler(
            $photographRepository,
            $imageDescriptionGenerator,
        )->__invoke($query);
    }
}
