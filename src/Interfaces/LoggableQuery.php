<?php

declare(strict_types=1);

namespace RemotelyLiving\PHPQueryBus\Interfaces;

use RemotelyLiving\PHPQueryBus\Enums\LogLevel;

interface LoggableQuery
{
    public function getLogContext(): array;

    public function getLogMessage(): string;

    public function getLogLevel(): LogLevel;
}
