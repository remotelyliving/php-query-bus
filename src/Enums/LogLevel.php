<?php

declare(strict_types=1);

namespace RemotelyLiving\PHPQueryBus\Enums;

use MyCLabs\Enum\Enum;

/**
 * @psalm-immutable
 *
 * @method static \RemotelyLiving\PHPQueryBus\Enums\LogLevel EMERGENCY()
 * @method static \RemotelyLiving\PHPQueryBus\Enums\LogLevel ALERT()
 * @method static \RemotelyLiving\PHPQueryBus\Enums\LogLevel CRITICAL()
 * @method static \RemotelyLiving\PHPQueryBus\Enums\LogLevel WARNING()
 * @method static \RemotelyLiving\PHPQueryBus\Enums\LogLevel NOTICE()
 * @method static \RemotelyLiving\PHPQueryBus\Enums\LogLevel INFO()
 * @method static \RemotelyLiving\PHPQueryBus\Enums\LogLevel DEBUG()
 */
final class LogLevel extends Enum
{
    private const EMERGENCY = 'emergency';
    private const ALERT = 'alert';
    private const CRITICAL = 'critical';
    private const WARNING = 'warning';
    private const NOTICE = 'notice';
    private const INFO = 'info';
    private const DEBUG = 'debug';
}
