<?php

namespace App\Application\Handler\Photograph;

use App\Application\Factory\Filename\Factory as FilenameFactory;
use App\Application\Commands\Photograph\CreateCommand;
use App\Application\Factory\FilePath\Factory as FilePathFactory;
use App\Application\Factory\Photograph\Factory as PhotographFactory;
use App\Application\Upload\FileUploader;
use App\Domain\Entity\Photograph;
use App\Domain\Repository\PhotographRepositoryInterface;

class CreateHandler
{
    public function __construct(
        private FilenameFactory                 $filenameFactory,
        private FilePathFactory                 $filePathFactory,
        private FileUploader                    $fileUploader,
        private PhotographFactory               $photographFactory,
        private PhotographRepositoryInterface   $photographRepository,
    ) {}

    public function __invoke(CreateCommand $command): Photograph
    {
        $uuid = $command->uuid();
        $file = $command->file();

        $filename = $this->filenameFactory->createFilename($uuid, $file);

        $filePath = $this->filePathFactory->create(
            filename: $filename,
        );

        $this->fileUploader->upload($file, $filename);

        $photograph = $this->photographFactory->createByCreateCommand($command, $filePath);
        $this->photographRepository->save($photograph);

        return $photograph;
    }
}
