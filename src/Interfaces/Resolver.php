<?php

declare(strict_types=1);

namespace RemotelyLiving\PHPQueryBus\Interfaces;

interface Resolver
{
    /**
     * @throws \RemotelyLiving\PHPQueryBus\Exceptions\OutOfBounds
     */
    public function resolve(object $query): Handler;

    public function pushHandler(string $queryClass, Handler $handler): self;

    public function pushHandlerDeferred(string $handler, callable $handlerFn): self;
}
