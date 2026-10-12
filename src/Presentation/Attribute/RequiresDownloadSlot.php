<?php

namespace App\Presentation\Attribute;

use Attribute;

/**
 * Lets the action run only while one of the server-wide download slots is free (PhotographDownloadSlots).
 * Claimed by DownloadSlotEventListener before the action runs, released after the response has been sent.
 *
 * Declare it below #[RateLimit]: attributes are handled in declaration order,
 * so a client over its rate limit never occupies a slot.
 */
#[Attribute(Attribute::TARGET_CLASS)]
final readonly class RequiresDownloadSlot
{
}
