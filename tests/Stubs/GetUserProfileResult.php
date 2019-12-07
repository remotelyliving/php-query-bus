<?php

declare(strict_types=1);

namespace RemotelyLiving\PHPQueryBus\Tests\Stubs;

use RemotelyLiving\PHPQueryBus\Interfaces\Result;

class GetUserProfileResult implements Result
{
    /**
     * @var array
     */
    private $preferences;

    /**
     * @var string
     */
    private $username;

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

    public function jsonSerialize(): array
    {
        return [
            'preferences' => $this->getPreferences(),
            'username' => $this->getUsername(),
        ];
    }
}
