<?php

declare(strict_types=1);

namespace RemotelyLiving\PHPQueryBus\Tests\Stubs;

use RemotelyLiving\PHPQueryBus\AbstractResult;

class GetUserProfileResult extends AbstractResult
{
    private array $preferences;

    private string $username;

    public function __construct(array $preferences, string $username)
    {
        $this->preferences = $preferences;
        $this->username = $username;
    }

    public function getPreferences(): array
    {
        return $this->preferences;
    }

    public function getUsername(): string
    {
        return $this->username;
    }
}
