<?php

declare(strict_types=1);

namespace RemotelyLiving\PHPQueryBus\Tests\Stubs;

use RemotelyLiving\PHPQueryBus\Interfaces\Query;
use RemotelyLiving\PHPQueryBus\Interfaces\Result;
use RemotelyLiving\PHPQueryBus\Interfaces\Handler;
use RemotelyLiving\PHPQueryBus\Interfaces\QueryBus;

class GetUserProfileHandler implements Handler
{
    public const PREFERENCES = ['foo' => 'bar'];
    public const USERNAME = 'christian.thomas';

    public function handle(Query $query, QueryBus $bus): Result
    {
        return new GetUserProfileResult(static::PREFERENCES, static::USERNAME);
    }
}
