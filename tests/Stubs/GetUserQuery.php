<?php

declare(strict_types=1);

namespace RemotelyLiving\PHPQueryBus\Tests\Stubs;

use RemotelyLiving\PHPQueryBus\Interfaces;

class GetUserQuery implements Interfaces\Query
{
    /**
     * @var bool
     */
    private $shouldGetProfile = false;

    /**
     * @var string
     */
    private $userId;

    public function __construct(string $userId)
    {
        $this->userId = $userId;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function addUserProfile(): self
    {
        $this->shouldGetProfile = true;
        return $this;
    }

    public function getGetUserProfileQuery(): ?GetUserProfileQuery
    {
        return ($this->shouldGetProfile)
            ? new GetUserProfileQuery($this->userId)
            : null;
    }
}
