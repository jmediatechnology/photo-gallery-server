<?php

namespace App\Fixtures;

use App\Domain\Entity\Photograph;
use App\Domain\ValueObject\CreatedAt;
use App\Domain\ValueObject\Description;
use App\Domain\ValueObject\FilePath;
use App\Domain\ValueObject\Title;
use App\Domain\ValueObject\UpdatedAt;
use App\Domain\ValueObject\UUID;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Yaml\Yaml;

class PhotographFixture extends Fixture
{
    private const string FIXTURE_IMAGES_DIR = 'fixtures/images';
    private const string FIXTURE_DATA_DIR = 'fixtures/data';

    public function __construct(
        private string $projectDir,
        private string $publicImageDir,
    ) {}

    public function load(ObjectManager $manager): void
    {
        foreach ($this->loadData() as $data) {

            $uuidString = (string) ($data['uuid'] ?? '');

            $uuid = new UUID($uuidString);
            $title = new Title($data['title']);
            $description = isset($data['description']) ? new Description($data['description']) : null;
            $filePath = new FilePath($data['filepath']);
            $createdAt = new CreatedAt(new DateTimeImmutable($data['createdAt']));
            $updatedAt = new UpdatedAt(new DateTimeImmutable($data['updatedAt']));

            $photograph = new Photograph(
                uuid: $uuid,
                title: $title,
                description: $description,
                filePath: $filePath,
                createdAt: $createdAt,
                updatedAt: $updatedAt,
            );
            $manager->persist($photograph);

            copy(
                from: $this->getFromFullPath($uuidString, '.jpg'),
                to: $this->getToFullPath($uuidString, '.jpg'),
            );
        }

        $manager->flush();
    }

    private function loadData(): array
    {
        $file = $this->projectDir . '/' . self::FIXTURE_DATA_DIR . '/photographs.yaml';
        return Yaml::parseFile($file);
    }

    private function getFromFullPath(string $uuid, string $extension): string
    {
        return strtr(
            ':projectDir/:fixtureImageDir/:uuid:extension',
            [
                ':projectDir' => $this->projectDir,
                ':fixtureImageDir' => self::FIXTURE_IMAGES_DIR,
                ':uuid' => $uuid,
                ':extension' => $extension,
            ],
        );
    }

    private function getToFullPath(string $uuid, string $extension): string
    {
        return strtr(
            ':publicImageDir/:uuid:extension',
            [
                ':projectDir' => $this->projectDir,
                ':publicImageDir' => $this->publicImageDir,
                ':uuid' => $uuid,
                ':extension' => $extension,
            ],
        );
    }
}
