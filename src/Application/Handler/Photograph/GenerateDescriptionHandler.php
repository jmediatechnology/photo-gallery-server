<?php

namespace App\Application\Handler\Photograph;

use App\Application\Query\Photograph\GenerateDescriptionForKnownPhotographUuidQuery;
use App\Domain\Entity\Photograph;
use App\Domain\ValueObject\FilePath;
use App\Infrastructure\Anthropic\Client\ImageDescriptionGeneratorInterface;
use App\Infrastructure\Doctrine\Repository\PhotographRepository;
use RuntimeException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use UnexpectedValueException;

#[AsMessageHandler]
class GenerateDescriptionHandler
{
    public function __construct(
        private PhotographRepository $photographRepository,
        private ImageDescriptionGeneratorInterface $imageDescriptionGenerator,
        private string $publicImageDir,
    ) {}

    public function __invoke(GenerateDescriptionForKnownPhotographUuidQuery $query): string
    {
        $photograph = $this->photographRepository->findOneBy([
            'uuid' => $query->uuid(),
        ]);

        if (!$photograph instanceof Photograph) {
            throw new UnexpectedValueException('Photograph not found');
        }

        $filePath = $photograph->filePath();

        return $this->imageDescriptionGenerator->describe(
            base64Image: $this->getBase64Image($filePath),
            mimeType: $this->getMimeType($filePath),
        );
    }

    private function getBase64Image(FilePath $filePath): string
    {
        $filename = $this->publicImageDir . '/' . $filePath->getBasename();
        if (!file_exists($filename)) {
            throw new RuntimeException(sprintf('File %s does not exist.', $filename));
        }
        $fileSystem = new Filesystem();
        $contents = $fileSystem->readFile($filename);
        return base64_encode($contents);
    }

    private function getMimeType(FilePath $filePath): string
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo === false) {
            throw new RuntimeException('Unable to open fileinfo.');
        }

        $filename = $this->publicImageDir . '/' . $filePath->getBasename();
        $mimeType = finfo_file($finfo, $filename);
        if ($mimeType === false) {
            throw new RuntimeException(sprintf('Unable to determine MIME type for "%s".', $filename));
        }

        return $mimeType;
    }
}
