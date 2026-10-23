<?php

namespace App\Tests\Unit\Application\Handlers\Photograph;

use App\Application\Handler\Photograph\GenerateDescriptionHandler;
use App\Application\Query\Photograph\GenerateDescriptionForKnownPhotographUuidQuery;
use App\Domain\Service\ImageDescriptionGeneratorInterface;
use App\Infrastructure\Doctrine\Repository\PhotographRepository;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

final class GenerateDescriptionHandlerTest extends TestCase
{
    #[Test]
    public function triggersUnexpectedValueExceptionWhenPhotographRepositoryCantFindPhotograph(): void
    {
        $this->expectException(UnexpectedValueException::class);

        $photographRepository = $this->createMock(PhotographRepository::class);
        $photographRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->willReturn(null);

        $imageDescriptionGenerator = $this->createStub(ImageDescriptionGeneratorInterface::class);

        $query = $this->createStub(GenerateDescriptionForKnownPhotographUuidQuery::class);

        new GenerateDescriptionHandler(
            $photographRepository,
            $imageDescriptionGenerator,
        )->__invoke($query);
    }
}
