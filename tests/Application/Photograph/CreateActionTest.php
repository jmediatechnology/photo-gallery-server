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
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class CreateActionTest extends ApiTestCase
{
    #[Test]
    #[DataProvider('provideUploadedFile')]
    public function canCreatePhotographWithUUID(UploadedFile $uploadedFile): void
    {
        $json = $this->request(
            method: 'POST',
            uri: '/photographs',
            parameters: [
                'uuid' => 'def58982-4487-4c9b-8f3a-f55865b45c3d',
                'title' => 'Title for canCreatePhotographWithUUID',
                'description' => 'Description for canCreatePhotographWithUUID',
            ],
            files: [
                $uploadedFile,
            ],
            server: [
                'HTTP_CONTENT_TYPE' => 'multipart/form-data',
            ]
        );

        self::assertResponseIsSuccessful();

        self::assertArrayHasKey('uuid', $json);
        self::assertArrayHasKey('title', $json);
        self::assertArrayHasKey('description', $json);
        self::assertArrayHasKey('filePath', $json);
        self::assertSame('def58982-4487-4c9b-8f3a-f55865b45c3d', $json['uuid']);
        self::assertSame('Title for canCreatePhotographWithUUID', $json['title']);
        self::assertSame('Description for canCreatePhotographWithUUID', $json['description']);
        self::assertSame('/images/' . $json['uuid'] . '.' . $uploadedFile->getExtension(), $json['filePath']);

        self::assertFileExists($this->publicImages . '/' . $json['uuid'] . '.' . $uploadedFile->getExtension());

        $photograph = $this->photographRepository->find('def58982-4487-4c9b-8f3a-f55865b45c3d');
        self::assertInstanceOf(Photograph::class, $photograph);
        self::assertEquals('def58982-4487-4c9b-8f3a-f55865b45c3d', $photograph->uuid()?->__toString());
        self::assertEquals('Title for canCreatePhotographWithUUID', $photograph->title()->__toString());
        self::assertEquals('Description for canCreatePhotographWithUUID', $photograph->description()?->__toString());
        self::assertSame('/images/' . $json['uuid'] . '.' . $uploadedFile->getExtension(), $photograph->filePath()?->__toString());
    }

    #[Test]
    #[DataProvider('provideUploadedFile')]
    public function canCreatePhotographWithoutUUID(UploadedFile $uploadedFile): void
    {
        $json = $this->request(
            method: 'POST',
            uri: '/photographs',
            parameters: [
                'title' => 'Title for canCreatePhotographWithoutUUID',
                'description' => 'Description for canCreatePhotographWithoutUUID',
            ],
            files: [
                $uploadedFile,
            ],
            server: [
                'HTTP_CONTENT_TYPE' => 'multipart/form-data',
            ]
        );

        self::assertResponseIsSuccessful();
        self::assertFileExists($this->publicImages . '/' . $uploadedFile->getFilename());

        self::assertArrayHasKey('uuid', $json);
        self::assertArrayHasKey('title', $json);
        self::assertArrayHasKey('description', $json);
        self::assertArrayHasKey('filePath', $json);
        self::assertHasValidUUID($json['uuid']);
        self::assertSame('Title for canCreatePhotographWithoutUUID', $json['title']);
        self::assertSame('Description for canCreatePhotographWithoutUUID', $json['description']);
        self::assertSame('/images/' . $uploadedFile->getFilename(), $json['filePath']);

        self::assertFileExists($this->publicImages . '/' . $uploadedFile->getFilename());

        $photograph = $this->photographRepository->find($json['uuid']);
        self::assertInstanceOf(Photograph::class, $photograph);
        self::assertHasValidUUID($photograph->uuid()?->__toString());
        self::assertSame('Title for canCreatePhotographWithoutUUID', $photograph->title()->__toString());
        self::assertSame('Description for canCreatePhotographWithoutUUID', $photograph->description()?->__toString());
        self::assertSame('/images/' . $uploadedFile->getFilename(), $photograph->filePath()?->__toString());
    }

    #[Test]
    #[DataProvider('provideUploadedFile')]
    public function doesNotAllowCreatingPhotographWhenTitleIsEmpty(UploadedFile $uploadedFile): void
    {
        $json = $this->request(
            method: 'POST',
            uri: '/photographs',
            parameters: [
                'title' => '',
                'description' => 'Description for doesNotAllowCreatingPhotographWhenTitleIsEmpty',
            ],
            files: [
                $uploadedFile,
            ],
            server: [
                'HTTP_CONTENT_TYPE' => 'multipart/form-data',
            ]
        );

        self::assertResponseStatusCodeSame(422);
        self::assertArrayHasKey('errors', $json);
        self::assertStringContainsString('This value should not be blank.', $json['errors']);
    }

    #[Test]
    #[DataProvider('provideUploadedFile')]
    public function doesNotAllowCreatingPhotographWhenTitleIsNotUnique(UploadedFile $uploadedFile): void
    {
        $photograph = new Photograph(
            uuid: new UUID('15cef954-39c7-4074-90fe-8fca7b3fdc3f'),
            title: new Title('Title for doesNotAllowCreatingPhotographWhenTitleIsNotUnique'),
            description: new Description('Description for doesNotAllowCreatingPhotographWhenTitleIsNotUnique'),
            filePath: new FilePath('/images/15cef954-39c7-4074-90fe-8fca7b3fdc3f.jpg'),
            createdAt: new CreatedAt(new DateTimeImmutable()),
            updatedAt: new UpdatedAt(new DateTimeImmutable()),
        );

        $this->photographRepository->save($photograph);

        $json = $this->request(
            method: 'POST',
            uri: '/photographs',
            parameters: [
                'title' => 'Title for doesNotAllowCreatingPhotographWhenTitleIsNotUnique',
                'description' => 'Description for doesNotAllowCreatingPhotographWhenTitleIsNotUnique',
            ],
            files: [
                $uploadedFile,
            ],
            server: [
                'HTTP_CONTENT_TYPE' => 'multipart/form-data',
            ]
        );

        self::assertResponseStatusCodeSame(422);
        self::assertArrayHasKey('errors', $json);
        self::assertStringContainsString('Title is already in use.', $json['errors']);
    }

    #[Test]
    public function doesNotAllowCreatingPhotographWhenFileIsNotUploaded(): void
    {
        $json = $this->request(
            method: 'POST',
            uri: '/photographs',
            parameters: [
                'uuid' => 'c85b47ed-15fd-4a90-81a0-3661a9401fc2',
                'title' => 'Title for doesNotAllowCreatingPhotographWhenFileIsNotUploaded',
                'description' => 'Description for doesNotAllowCreatingPhotographWhenFileIsNotUploaded',
            ],
            server: [
                'HTTP_CONTENT_TYPE' => 'multipart/form-data',
            ]
        );

        self::assertResponseStatusCodeSame(422);
        self::assertArrayHasKey('errors', $json);
        self::assertStringContainsString('You must upload exactly one file.', $json['errors']);
    }
}
