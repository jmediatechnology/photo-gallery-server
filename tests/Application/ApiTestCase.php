<?php

namespace App\Tests\Application;

use App\Infrastructure\Doctrine\Repository\PhotographRepository;
use App\Infrastructure\Symfony\Security\User\AnonymousUser;
use App\Infrastructure\Symfony\Security\User\User;
use App\Kernel;
use App\Tests\Utils\ObjectMother\UploadedFileMother;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Platforms\SqlitePlatform;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use ReflectionClass;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

abstract class ApiTestCase extends WebTestCase
{
    private static ?string $projectDir = null;

    private const string TEST_FILE_NAME = 'portrait-of-dora-maar.jpg';

    protected KernelBrowser $client;
    protected PhotographRepository $photographRepository;
    protected string $publicImages = '';

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->client = static::createClient();

        $this->photographRepository = static::getContainer()->get(PhotographRepository::class);

        if ($this->isUsingSqlite()) {
            $this->recreateSqliteSchema();
        }

        self::copyTestFile();

        $this->publicImages = static::getContainer()->getParameter('public_images_dir');
    }

    protected function createAnonymousAuthenticatedClient(): KernelBrowser
    {
        $jwtManager = static::getContainer()->get(JWTTokenManagerInterface::class);
        assert($jwtManager instanceof JWTTokenManagerInterface);

        $user = new AnonymousUser();
        $payload = [
            'anon_id' => uniqid('anon_', true),
        ];
        $token = $jwtManager->createFromPayload($user, $payload);

        $this->client->setServerParameter('HTTP_Authorization', sprintf('Bearer %s', $token));

        return $this->client;
    }

    protected function createAuthenticatedClient(): KernelBrowser
    {
        $jwtManager = static::getContainer()->get(JWTTokenManagerInterface::class);
        $userPasswordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        assert($jwtManager instanceof JWTTokenManagerInterface);
        assert($userPasswordHasher instanceof UserPasswordHasherInterface);

        $hashedPassword = $userPasswordHasher->hashPassword(
            user: new User(
                username: 'test_user',
                password: 'test_user_password',
                roles: ['ROLE_ADMIN'],
            ),
            plainPassword: 'test_user_password'
        );

        $user = new User(
            username: 'test_user',
            password: $hashedPassword,
            roles: ['ROLE_ADMIN'],
        );
        $token = $jwtManager->createFromPayload($user);

        $this->client->setServerParameter('HTTP_Authorization', sprintf('Bearer %s', $token));

        return $this->client;
    }

    protected function requestAsAnonymousUser(string $method, string $uri, array $parameters = [], array $files = []): array
    {
        $client = $this->createAnonymousAuthenticatedClient();
        $client->request(
            method: $method,
            uri: $uri,
            parameters: $parameters,
            files: $files,
        );

        $content = $client->getResponse()->getContent();

        if (empty($content)) {
            return [];
        }

        return json_decode($content, true);
    }

    protected function request(
        string $method,
        string $uri,
        array $parameters = [],
        array $files = [],
        array $server = [],
    ): array
    {
        $client = $this->createAuthenticatedClient();
        $client->request(
            method: $method,
            uri: $uri,
            parameters: $parameters,
            files: $files,
            server: $server,
        );

        $content = $client->getResponse()->getContent();

        if (empty($content)) {
            return [];
        }

        return json_decode($content, true);
    }

    protected function jsonRequest(string $method, string $uri, array $parameters = []): array
    {
        $client = $this->createAuthenticatedClient();
        $client->jsonRequest(
            method: $method,
            uri: $uri,
            parameters: $parameters,
        );

        $content = $client->getResponse()->getContent();

        if (empty($content)) {
            return [];
        }

        return json_decode($content, true);
    }

    protected static function assertHasValidUUID(string $uuid): void
    {
        self::assertMatchesRegularExpression(
            pattern: '/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            string: $uuid,
        );
    }

    /**
     * @return array<array-key, array<array-key, UploadedFile>>
     */
    public static function provideUploadedFile(): array
    {
        self::copyTestFile();

        return [
            [UploadedFileMother::create(
                path: self::getFullPathToTemporaryTestFile(),
                originalName: self::TEST_FILE_NAME,
            )],
        ];
    }

    /**
     * @throws Exception
     */
    private function isUsingSqlite(): bool
    {
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $connection = $em->getConnection();
        $databasePlatform = $connection->getDatabasePlatform();
        return $databasePlatform instanceof SqlitePlatform;
    }

    private function recreateSqliteSchema(): void
    {
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $metadata = $em->getMetadataFactory()->getAllMetadata();

        $schemaTool = new SchemaTool($em);
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);
    }

    private static function getProjectDir(): string
    {
        if (self::$projectDir === null) {
            $kernelFile = new ReflectionClass(Kernel::class)->getFileName();
            self::$projectDir = dirname($kernelFile, 2);
        }

        return self::$projectDir;
    }

    private static function getFullPathToTestFile(): string
    {
        $fixtureImagesDir = self::getProjectDir() . '/fixtures/images/';
        return $fixtureImagesDir . self::TEST_FILE_NAME;
    }

    private static function getFullPathToTemporaryTestFile(): string
    {
        return self::getProjectDir() . '/var/tmp/' . self::TEST_FILE_NAME;
    }

    private static function copyTestFile(): void
    {
        $fullPathToTestFile = self::getFullPathToTestFile();
        $fullPathToTemporaryTestFile = self::getFullPathToTemporaryTestFile();

        if (!file_exists($fullPathToTestFile)) {
            throw new FileNotFoundException(sprintf('Error: unable to find "%s"', $fullPathToTestFile));
        }

        $filesystem = new Filesystem();
        $filesystem->copy(
            originFile: $fullPathToTestFile,
            targetFile: $fullPathToTemporaryTestFile,
        );

        if (!file_exists($fullPathToTemporaryTestFile)) {
            throw new FileNotFoundException(sprintf('Error: unable to find "%s"', $fullPathToTemporaryTestFile));
        }
    }
}
