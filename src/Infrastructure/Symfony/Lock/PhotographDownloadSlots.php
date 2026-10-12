<?php

namespace App\Infrastructure\Symfony\Lock;

use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\LockInterface;

/**
 * Caps how many downloads stream at the same time, server-wide.
 *
 * A download holds an Apache worker for as long as the transfer takes. The rate limiter counts downloads per hour,
 * not how many run at once, so without this cap a few visitors could occupy every worker and block browsing.
 *
 * Each slot is a lock. With the flock store the kernel releases a lock when its process dies,
 * so a crashed request can never keep a slot taken.
 */
class PhotographDownloadSlots
{
    /**
     * 8 of the 40 Apache workers (docker/apache/mpm_prefork.conf), leaving 32 for everything else.
     */
    public const int COUNT = 8;

    private const string RESOURCE_PREFIX = 'photograph-download-slot-';

    public function __construct(private LockFactory $lockFactory) {}

    /**
     * Returns a held lock for a free slot, or null when every slot is busy. Never waits.
     * The caller must release the lock when the download ends.
     */
    public function claim(): ?LockInterface
    {
        for ($slotNumber = 0; $slotNumber < self::COUNT; $slotNumber++) {
            $lock = $this->lockFactory->createLock(self::RESOURCE_PREFIX . $slotNumber);
            if ($lock->acquire()) {
                return $lock;
            }
        }

        return null;
    }
}
