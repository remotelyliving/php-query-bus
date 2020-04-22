<?php

declare(strict_types=1);

namespace RemotelyLiving\PHPQueryBus\Tests\Stubs;

use RemotelyLiving\PHPQueryBus\AbstractResult;
use RemotelyLiving\PHPQueryBus\Interfaces;

class GetUserProfileHandler implements Interfaces\Handler
{
    public const PREFERENCES = ['foo' => 'bar'];
    public const USERNAME = 'christian.thomas';

    public function handle(object $query, Interfaces\QueryBus $bus): Interfaces\Result
    {
        if ($query->shouldHandlerReturnWithErrors()) {
            return AbstractResult::withErrors(
                new \LogicException('Something went really wrong'),
                new \InvalidArgumentException('But seriously')
            );
        }

        return new GetUserProfileResult(static::PREFERENCES, static::USERNAME);
    }
}
