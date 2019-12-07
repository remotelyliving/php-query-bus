<?php

declare(strict_types=1);

namespace RemotelyLiving\PHPQueryBus\Tests\Stubs;

use RemotelyLiving\PHPQueryBus\Interfaces\Query;
use RemotelyLiving\PHPQueryBus\Interfaces\Result;
use RemotelyLiving\PHPQueryBus\Interfaces\Handler;
use RemotelyLiving\PHPQueryBus\Interfaces\QueryBus;

class GetUserHandler implements Handler
{
    public function handle(Query $query, QueryBus $bus): Result
    {
        $user = new \stdClass();
        $user->id = $query->getUserId();

        /** @var \RemotelyLiving\PHPQueryBus\Tests\Stubs\GetUserQuery $query */
        return ($query->getGetUserProfileQuery())
            ? new GetUserResult($user, $bus->handle($query->getGetUserProfileQuery()))
            : new GetUserResult($user);
    }
}
