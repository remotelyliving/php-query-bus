<?php

declare(strict_types=1);

namespace RemotelyLiving\PHPQueryBus\Tests\Stubs;

use RemotelyLiving\PHPQueryBus\Interfaces;

class GetUserQuery implements Interfaces\Query
{
    private bool $shouldGetProfile = false;

    private string $userId;

    public function __construct(string $userId)
    {
        $this->userId = $userId;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function includeProfile(): self
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
