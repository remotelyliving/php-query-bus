<?php

declare(strict_types=1);

namespace RemotelyLiving\PHPQueryBus\Tests\Stubs;

use RemotelyLiving\PHPQueryBus\Interfaces\CacheableQuery;
use RemotelyLiving\PHPQueryBus\Interfaces\LoggableQuery;
use RemotelyLiving\PHPQueryBus\Enums\LogLevel;

class GetUserProfileQuery implements LoggableQuery, CacheableQuery
{
    public const LOG_CONTEXT = ['foo' => 'bar'];
    public const LOG_MESSAGE = 'User profile retrieved';
    public const LOG_LEVEL = 'info';

    public const CACHE_TTL = 123;
    public const CACHE_KEY = 'cacheKey';

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

    public function getLogContext(): array
    {
        return static::LOG_CONTEXT;
    }

    public function getLogLevel(): LogLevel
    {
        return new LogLevel(static::LOG_LEVEL);
    }

    public function getLogMessage(): string
    {
        return static::LOG_MESSAGE;
    }

    public function getTTL(): int
    {
        return static::CACHE_TTL;
    }

    public function getCacheKey(): string
    {
        return static::CACHE_KEY;
    }
}
