<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Symfony\Lock;

use App\Infrastructure\Symfony\Lock\PhotographDownloadSlots;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\LockInterface;
use Symfony\Component\Lock\Store\FlockStore;

/**
 * Uses the real flock store, in a directory of its own, so the test proves actual mutual exclusion.
 */
final class PhotographDownloadSlotsTest extends TestCase
{
    private string $lockDir;
    private PhotographDownloadSlots $downloadSlots;

    /** @var list<LockInterface|null> */
    private array $claimedSlots = [];

    protected function setUp(): void
    {
        $this->lockDir = sys_get_temp_dir() . '/photograph-download-slots-test-' . uniqid();
        new Filesystem()->mkdir($this->lockDir);

        $this->downloadSlots = new PhotographDownloadSlots(new LockFactory(new FlockStore($this->lockDir)));
    }

    protected function tearDown(): void
    {
        foreach ($this->claimedSlots as $slot) {
            $slot?->release();
        }

        new Filesystem()->remove($this->lockDir);
    }

    #[Test]
    public function handsOutEverySlotOnce(): void
    {
        $this->claimSlots(PhotographDownloadSlots::COUNT);

        self::assertNotContains(null, $this->claimedSlots);
        self::assertContainsOnlyInstancesOf(LockInterface::class, $this->claimedSlots);
    }

    #[Test]
    public function returnsNullWhenEverySlotIsTaken(): void
    {
        $this->claimSlots(PhotographDownloadSlots::COUNT);

        self::assertNull($this->downloadSlots->claim());
    }

    #[Test]
    public function aReleasedSlotCanBeClaimedAgain(): void
    {
        $this->claimSlots(PhotographDownloadSlots::COUNT);
        $this->claimedSlots[0]->release();

        $slot = $this->downloadSlots->claim();
        $this->claimedSlots[] = $slot;

        self::assertInstanceOf(LockInterface::class, $slot);
    }

    private function claimSlots(int $count): void
    {
        for ($claim = 1; $claim <= $count; $claim++) {
            $this->claimedSlots[] = $this->downloadSlots->claim();
        }
    }
}
