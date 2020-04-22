<?php

declare(strict_types=1);

namespace RemotelyLiving\PHPQueryBus\Tests\Stubs;

use RemotelyLiving\PHPQueryBus\AbstractResult;

class GetUserResult extends AbstractResult
{
    private \stdClass $user;

    private ?GetUserProfileResult $userProfileResult;

    public function __construct(\stdClass $user, GetUserProfileResult $userProfileResult = null)
    {
        $this->user = $user;
        $this->userProfileResult = $userProfileResult;
    }

    public function getUser(): \stdClass
    {
        return $this->user;
    }

    public function getUserProfileResult(): ?GetUserProfileResult
    {
        return $this->userProfileResult;
    }
}
