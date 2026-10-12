<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Symfony\Event;

use App\Infrastructure\Symfony\Event\DownloadSlotEventListener;
use App\Infrastructure\Symfony\Lock\PhotographDownloadSlots;
use App\Presentation\Attribute\RequiresDownloadSlot;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\Event\ControllerAttributeEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Lock\LockInterface;

class DownloadSlotEventListenerTest extends TestCase
{
    #[Test]
    public function releasesTheClaimedSlotWhenTheKernelTerminates(): void
    {
        $slot = $this->createMock(LockInterface::class);
        $slot
            ->expects($this->once())
            ->method('release');

        $downloadSlots = $this->createMock(PhotographDownloadSlots::class);
        $downloadSlots
            ->expects($this->once())
            ->method('claim')
            ->willReturn($slot);

        $request = new Request();
        $listener = new DownloadSlotEventListener($downloadSlots);

        $listener->claimSlot($this->createControllerAttributeEvent($request));
        $listener->releaseSlot($this->createTerminateEvent($request));
    }

    #[Test]
    public function refusesTheRequestWhenEverySlotIsTaken(): void
    {
        $downloadSlots = $this->createStub(PhotographDownloadSlots::class);
        $downloadSlots
            ->method('claim')
            ->willReturn(null);

        try {
            new DownloadSlotEventListener($downloadSlots)->claimSlot($this->createControllerAttributeEvent(new Request()));
            self::fail('Expected a ServiceUnavailableHttpException, but none was thrown.');
        } catch (ServiceUnavailableHttpException $exception) {
            self::assertSame(Response::HTTP_SERVICE_UNAVAILABLE, $exception->getStatusCode());
            self::assertSame(['Retry-After' => 30], $exception->getHeaders());
            self::assertSame('Too many downloads in progress. Try again in a moment', $exception->getMessage());
        }
    }

    #[Test]
    public function releasesNothingForARequestThatHoldsNoSlot(): void
    {
        $downloadSlots = $this->createMock(PhotographDownloadSlots::class);
        $downloadSlots
            ->expects($this->never())
            ->method('claim');

        new DownloadSlotEventListener($downloadSlots)->releaseSlot($this->createTerminateEvent(new Request()));
    }

    /**
     * @return ControllerAttributeEvent<RequiresDownloadSlot, ControllerArgumentsEvent>
     */
    private function createControllerAttributeEvent(Request $request): ControllerAttributeEvent
    {
        return new ControllerAttributeEvent(
            new RequiresDownloadSlot(),
            new ControllerArgumentsEvent(
                $this->createStub(HttpKernelInterface::class),
                static fn (): Response => new Response(),
                [],
                $request,
                HttpKernelInterface::MAIN_REQUEST,
            ),
        );
    }

    private function createTerminateEvent(Request $request): TerminateEvent
    {
        return new TerminateEvent($this->createStub(HttpKernelInterface::class), $request, new Response());
    }
}
