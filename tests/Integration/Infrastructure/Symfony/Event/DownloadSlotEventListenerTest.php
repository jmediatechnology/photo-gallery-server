<?php

namespace App\Tests\Integration\Infrastructure\Symfony\Event;

use App\Infrastructure\Symfony\Event\DownloadSlotEventListener;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class DownloadSlotEventListenerTest extends KernelTestCase
{
    #[Test]
    #[DataProvider('listenedEvents')]
    public function listensToTheEvent(string $event, string $method): void
    {
        $dispatcher = self::getContainer()->get(EventDispatcherInterface::class);

        $listeners = array_filter(
            $dispatcher->getListeners($event),
            static fn (callable $listener): bool => is_array($listener)
                && $listener[0] instanceof DownloadSlotEventListener
                && $listener[1] === $method,
        );

        self::assertCount(1, $listeners);
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function listenedEvents(): iterable
    {
        yield 'claims a slot for an action marked #[RequiresDownloadSlot]' => [
            DownloadSlotEventListener::REQUIRES_DOWNLOAD_SLOT_EVENT,
            'claimSlot',
        ];
        yield 'releases the slot when the kernel terminates' => [
            KernelEvents::TERMINATE,
            'releaseSlot',
        ];
    }
}
