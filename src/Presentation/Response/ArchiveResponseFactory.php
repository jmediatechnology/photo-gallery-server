<?php

namespace App\Presentation\Response;

use App\Application\ValueObject\PhotographArchive;
use App\Infrastructure\ZipStream\ZipArchiveWriter;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ArchiveResponseFactory
{
    public function __construct(
        private ZipArchiveWriter $zipArchiveWriter,
    ) {}

    public function create(PhotographArchive $archive): StreamedResponse
    {
        return new StreamedResponse(
            callbackOrChunks: fn () => $this->zipArchiveWriter->stream($archive),
            headers: [
                'Content-Type' => 'application/zip',
                'Content-Length' => (string) $this->zipArchiveWriter->size($archive),
                'Content-Disposition' => HeaderUtils::makeDisposition(
                    disposition: HeaderUtils::DISPOSITION_ATTACHMENT,
                    filename: $archive->name(),
                ),
            ],
        );
    }
}
