<?php

declare(strict_types=1);

namespace RemotelyLiving\PHPQueryBus\Tests\Stubs;

use RemotelyLiving\PHPQueryBus\Interfaces;

class GetUserHandler implements Interfaces\Handler
{
    public function handle(object $query, Interfaces\QueryBus $bus): Interfaces\Result
    {
        $user = new \stdClass();
        $user->id = $query->getUserId();

        /** @var \RemotelyLiving\PHPQueryBus\Tests\Stubs\GetUserQuery $query */
        return ($query->getGetUserProfileQuery())
            ? new GetUserResult($user, $bus->handle($query->getGetUserProfileQuery()))
            : new GetUserResult($user);
    }
}
