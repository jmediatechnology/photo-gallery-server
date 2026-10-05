<?php

namespace App\Tests\Unit\Application\Handlers\Photograph;

use App\Application\Handler\Photograph\GetHandler;
use App\Application\Query\Photograph\GetQuery;
use App\Domain\Entity\Photograph;
use App\Domain\Enum\PhotographSortDirection;
use App\Domain\Enum\PhotographSortField;
use App\Infrastructure\Doctrine\Repository\PhotographRepository;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class GetHandlerTest extends TestCase
{
    #[Test]
    public function canFindAll(): void
    {
        $title = null;

        $query = $this->createMock(GetQuery::class);
        $query
            ->expects($this->once())
            ->method('title')
            ->willReturn($title);
        $query
            ->expects($this->once())
            ->method('sortField')
            ->willReturn(PhotographSortField::CreatedAt);
        $query
            ->expects($this->once())
            ->method('sortDirection')
            ->willReturn(PhotographSortDirection::Desc);

        $photographs = [
            $this->createStub(Photograph::class),
            $this->createStub(Photograph::class),
            $this->createStub(Photograph::class),
        ];

        $photographRepository = $this->createMock(PhotographRepository::class);
        $photographRepository
            ->expects($this->once())
            ->method('findByCriteria')
            ->willReturn($photographs);

        $actual = new GetHandler($photographRepository)->__invoke($query);

        $this->assertContainsOnlyInstancesOf(Photograph::class, $actual);
        $this->assertSame($photographs, $actual);
    }

    #[Test]
    public function canFindByTitle(): void
    {
        $title = 'Awesome Title';

        $query = $this->createMock(GetQuery::class);
        $query
            ->expects($this->once())
            ->method('title')
            ->willReturn($title);
        $query
            ->expects($this->once())
            ->method('sortField')
            ->willReturn(PhotographSortField::CreatedAt);
        $query
            ->expects($this->once())
            ->method('sortDirection')
            ->willReturn(PhotographSortDirection::Desc);

        $photographs = [
            $this->createStub(Photograph::class),
            $this->createStub(Photograph::class),
            $this->createStub(Photograph::class),
        ];

        $photographRepository = $this->createMock(PhotographRepository::class);
        $photographRepository
            ->expects($this->once())
            ->method('findByCriteria')
            ->willReturn($photographs);

        $actual = new GetHandler($photographRepository)->__invoke($query);

        $this->assertContainsOnlyInstancesOf(Photograph::class, $actual);
        $this->assertSame($photographs, $actual);
    }
}
