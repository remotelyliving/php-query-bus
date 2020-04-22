<?php

declare(strict_types=1);

namespace RemotelyLiving\PHPQueryBus\Tests\Stubs;

use MyCLabs\Enum\Enum;
use RemotelyLiving\PHPQueryBus\Interfaces;
use RemotelyLiving\PHPQueryBus\Enums;

class GetUserProfileQuery implements Interfaces\LoggableQuery, Interfaces\CacheableQuery
{
    public const LOG_CONTEXT = ['foo' => 'bar'];
    public const LOG_MESSAGE = 'User profile retrieved';
    public const LOG_LEVEL = 'info';

    public const CACHE_TTL = 123;
    public const CACHE_KEY = 'cacheKey';

    private string $userId;

    private bool $handlerShouldReturnWithErrors = false;

    public function __construct(string $userId)
    {
        $this->userId = $userId;
    }

    public function handlerShouldReturnWithErrors(): self
    {
        $clone = clone $this;
        $clone->handlerShouldReturnWithErrors = true;

        return $clone;
    }

    public function shouldHandlerReturnWithErrors(): bool
    {
        return $this->handlerShouldReturnWithErrors;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getLogContext(): array
    {
        return static::LOG_CONTEXT;
    }

    public function getLogLevel(): Enums\LogLevel
    {
        return new Enums\LogLevel(static::LOG_LEVEL);
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

    public function shouldRecomputeResult(): bool
    {
        return false;
    }
}
