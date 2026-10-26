<?php

namespace App\Domain\Enum;

enum ImageDescriptionGenerationFailureReason
{
    /** The photograph exceeds the provider's image limit. The user needs to upload a smaller image file. */
    case ImageTooLarge;

    /** The provider is rate limiting us. */
    case ProviderBusy;

    /** The provider is down or overloaded. */
    case ProviderUnavailable;
}
