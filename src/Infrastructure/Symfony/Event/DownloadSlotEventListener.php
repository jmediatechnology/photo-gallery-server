<?php

namespace App\Infrastructure\Symfony\Event;

use App\Infrastructure\Symfony\Lock\PhotographDownloadSlots;
use App\Presentation\Attribute\RequiresDownloadSlot;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\Event\ControllerAttributeEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Lock\LockInterface;

/**
 * Holds one download slot for the whole lifetime of a request to an action marked #[RequiresDownloadSlot].
 *
 * If streaming fails, kernel.terminate never runs. The slot is released anyway: the lock lives on the request,
 * and Symfony's Lock releases itself when it is destroyed at the end of the request.
 */
final readonly class DownloadSlotEventListener
{
    public const string REQUIRES_DOWNLOAD_SLOT_EVENT = KernelEvents::CONTROLLER_ARGUMENTS . '.' . RequiresDownloadSlot::class;

    private const string SLOT_REQUEST_ATTRIBUTE = '_download_slot';
    private const int RETRY_AFTER_SECONDS = 30;

    public function __construct(private PhotographDownloadSlots $downloadSlots) {}

    /**
     * @param ControllerAttributeEvent<RequiresDownloadSlot, ControllerArgumentsEvent> $event
     */
    public function claimSlot(ControllerAttributeEvent $event): void
    {
        $slot = $this->downloadSlots->claim();
        if ($slot === null) {
            throw new ServiceUnavailableHttpException(
                retryAfter: self::RETRY_AFTER_SECONDS,
                message: 'Too many downloads in progress. Try again in a moment',
            );
        }

        $event->kernelEvent->getRequest()->attributes->set(self::SLOT_REQUEST_ATTRIBUTE, $slot);
    }

    /**
     * Runs after the response, and so the whole zip, has been sent.
     */
    public function releaseSlot(TerminateEvent $event): void
    {
        $slot = $event->getRequest()->attributes->get(self::SLOT_REQUEST_ATTRIBUTE);
        if ($slot instanceof LockInterface) {
            $slot->release();
        }
    }
}
