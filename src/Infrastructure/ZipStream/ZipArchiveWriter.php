<?php

namespace App\Infrastructure\ZipStream;

use App\Application\ValueObject\PhotographArchive;
use RuntimeException;
use ZipStream\CompressionMethod;
use ZipStream\Exception as ZipStreamException;
use ZipStream\OperationMode;
use ZipStream\ZipStream;

final readonly class ZipArchiveWriter
{
    /**
     * Builds the archive in simulation mode: no file content is read, only headers are calculated.
     * A ZipStream instance is bound to one operation mode, so stream() builds its own.
     * Calculates the exact byte size of the archive without reading any file.
     *
     * @throws RuntimeException
     */
    public function size(PhotographArchive $archive): int
    {
        try {
            return $this->createZip($archive, OperationMode::SIMULATE_STRICT)->finish();
        } catch (ZipStreamException $exception) {
            throw new RuntimeException('Unable to prepare the archive.', previous: $exception);
        }
    }

    /**
     * Writes the archive to the output buffer.
     *
     * @throws RuntimeException
     */
    public function stream(PhotographArchive $archive): void
    {
        try {
            $this->createZip($archive, OperationMode::NORMAL)->finish();
        } catch (ZipStreamException $exception) {
            throw new RuntimeException('Unable to stream the archive.', previous: $exception);
        }
    }

    /**
     * @throws ZipStreamException
     */
    private function createZip(PhotographArchive $archive, OperationMode $operationMode): ZipStream
    {
        $zipStream = new ZipStream(
            operationMode: $operationMode,
            defaultCompressionMethod: CompressionMethod::STORE,
            sendHttpHeaders: false,
        );

        foreach ($archive->entries() as $entry) {
            $zipStream->addFileFromPath(
                fileName: $entry->name(),
                path: $entry->path(),
                exactSize: $entry->size(),
            );
        }

        return $zipStream;
    }
}
