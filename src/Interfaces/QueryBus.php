<?php

declare(strict_types=1);

namespace RemotelyLiving\PHPQueryBus\Interfaces;

interface QueryBus
{
    public function handle(object $query): Result;

    public function pushMiddleware(callable $middleware): self;
}
