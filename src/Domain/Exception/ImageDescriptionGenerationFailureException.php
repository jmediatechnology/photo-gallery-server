<?php

namespace App\Domain\Exception;

use App\Domain\Enum\ImageDescriptionGenerationFailureReason;
use DomainException;
use Throwable;

class ImageDescriptionGenerationFailureException extends DomainException
{
    public function __construct(
        public readonly ImageDescriptionGenerationFailureReason $reason,
        public readonly ?int $retryAfter = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct(sprintf('Image description generation failed: %s.', $reason->name), previous: $previous);
    }
}
