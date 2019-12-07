<?php

declare(strict_types=1);

namespace RemotelyLiving\PHPQueryBus\Traits;

use Psr\Log;

trait Logger
{
    use Log\LoggerAwareTrait;

    protected function getLogger(): Log\LoggerInterface
    {
        if ($this->logger === null) {
            $this->logger = new Log\NullLogger();
        }

        return $this->logger;
    }
}
