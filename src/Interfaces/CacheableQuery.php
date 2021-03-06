<?php

declare(strict_types=1);

namespace RemotelyLiving\PHPQueryBus\Interfaces;

interface CacheableQuery
{
    public function getCacheKey(): string;

    public function shouldRecomputeResult(): bool;

    public function getTTL(): int;
}
