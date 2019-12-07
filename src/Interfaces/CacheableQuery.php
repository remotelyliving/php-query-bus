<?php

declare(strict_types=1);

namespace RemotelyLiving\PHPQueryBus\Interfaces;

interface CacheableQuery extends Query
{
    public function getCacheKey(): string;

    public function getTTL(): int;
}
