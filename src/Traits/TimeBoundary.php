<?php

declare(strict_types=1);

namespace RemotelyLiving\PHPQueryBus\Traits;

trait TimeBoundary
{
    private ?int $time;

    protected function getTimeStamp(): int
    {
        if (!isset($this->time)) {
            $this->time = time();
        }

        return $this->time;
    }

    public function setTimeStamp(?int $time): void
    {
        $this->time = $time;
    }
}
