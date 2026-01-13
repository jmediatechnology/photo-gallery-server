<?php

declare(strict_types=1);

namespace App\Tests\Application\Photograph;

use App\Domain\Entity\Photograph;
use App\Domain\ValueObject\CreatedAt;
use App\Domain\ValueObject\Description;
use App\Domain\ValueObject\FilePath;
use App\Domain\ValueObject\Title;
use App\Domain\ValueObject\UpdatedAt;
use App\Domain\ValueObject\UUID;
use App\Tests\Application\ApiTestCase;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;

class DeleteActionTest extends ApiTestCase
{
    #[Test]
    public function canDeletePhotograph(): void
    {
        $photograph = new Photograph(
            uuid: new UUID('b3ce9363-c498-432e-ba25-c7f5844c6dc4'),
            title: new Title('Title for canDeletePhotograph'),
            description: new Description('Description for canDeletePhotograph'),
            filePath: new FilePath('public/images/b3ce9363-c498-432e-ba25-c7f5844c6dc4.jpg'),
            createdAt: new CreatedAt(new DateTimeImmutable()),
            updatedAt: new UpdatedAt(new DateTimeImmutable()),
        );

        $this->photographRepository->save($photograph);

        $json = $this->jsonRequest(
            method: 'DELETE',
            uri: '/photographs/b3ce9363-c498-432e-ba25-c7f5844c6dc4',
        );

        self::assertEmpty($json);
        self::assertResponseStatusCodeSame(204);

        $photograph = $this->photographRepository->find('b3ce9363-c498-432e-ba25-c7f5844c6dc4');
        $this->assertNull($photograph);
    }
}
