<?php

declare(strict_types=1);

namespace RemotelyLiving\PHPQueryBus\Interfaces;

interface Handler
{
    public function handle(Query $query, QueryBus $bus): Result;
}
