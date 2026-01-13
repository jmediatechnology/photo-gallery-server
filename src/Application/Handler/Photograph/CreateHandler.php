<?php

namespace App\Application\Handler\Photograph;

use App\Application\Commands\Photograph\CreateCommand;
use App\Application\Factory\FilePath\Factory as FilePathFactory;
use App\Application\Factory\Photograph\Factory;
use App\Application\Upload\FileUploader;
use App\Domain\Entity\Photograph;
use App\Infrastructure\Doctrine\Repository\PhotographRepository;

class CreateHandler
{
    public function __construct(
        private PhotographRepository $photographRepository,
        private Factory              $photographFactory,
        private FileUploader         $fileUploader,
        private FilePathFactory      $filePathFactory,
    ) {}

    public function __invoke(CreateCommand $command): Photograph
    {
        $file = $command->file();

        $uuid = $command->uuid();
        $filename = $uuid !== null
            ? $uuid . '.' . $file->getExtension()
            : $file->getClientOriginalName();

        $filePath = $this->filePathFactory->create(
            filename: $filename,
        );

        $this->fileUploader->upload($file, $filename);

        $photograph = $this->photographFactory->createByCreateCommand($command, $filePath);
        $this->photographRepository->save($photograph);

        return $photograph;
    }
}
