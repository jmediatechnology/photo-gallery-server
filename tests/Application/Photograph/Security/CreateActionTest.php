<?php

declare(strict_types=1);

namespace App\Tests\Application\Photograph\Security;

use App\Tests\Application\ApiTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class CreateActionTest extends ApiTestCase
{
    #[Test]
    #[DataProvider('provideUploadedFile')]
    public function doesNotAllowCreatingPhotographWhenUserIsNotAdmin(UploadedFile $uploadedFile): void
    {
        $json = $this->requestAsAnonymousUser(
            method: 'POST',
            uri: '/photographs',
            parameters: [
                'uuid' => 'dc664668-79c0-44f4-91e6-6692c6a732a6',
                'title' => 'Title for doesNotAllowCreatingPhotographWhenUserIsNotAdmin',
                'description' => 'Description for doesNotAllowCreatingPhotographWhenUserIsNotAdmin',
            ],
            files: [
                $uploadedFile,
            ],
        );

        self::assertResponseStatusCodeSame(423);
        self::assertArrayHasKey('errors', $json);
        self::assertStringContainsString('Access Denied', $json['errors']);
    }
}
