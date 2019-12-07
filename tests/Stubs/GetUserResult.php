<?php

declare(strict_types=1);

namespace RemotelyLiving\PHPQueryBus\Tests\Stubs;

use RemotelyLiving\PHPQueryBus\Interfaces\Result;

class GetUserResult implements Result
{

    /**
     * @var \stdClass|null
     */
    private $user;

    /**
     * @var \RemotelyLiving\PHPQueryBus\Tests\Stubs\GetUserProfileResult|null
     */
    private $userProfileResult;

    public function __construct(?\stdClass $user, GetUserProfileResult $userProfileResult = null)
    {
        $this->user = $user;
        $this->userProfileResult = $userProfileResult;
    }

    public function getUser(): ?\stdClass
    {
        return $this->user;
    }

    public function getUserProfileResult(): ?GetUserProfileResult
    {
        return $this->userProfileResult;
    }

    public function jsonSerialize(): array
    {
        return [
            'user' => $this->getUser(),
            'profile' => $this->getUserProfileResult(),
        ];
    }
}
