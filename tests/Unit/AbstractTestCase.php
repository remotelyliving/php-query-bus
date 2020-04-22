<?php

declare(strict_types=1);

namespace RemotelyLiving\PHPQueryBus\Tests\Unit;

use PHPUnit;
use Psr\Log;

abstract class AbstractTestCase extends PHPUnit\Framework\TestCase
{
    public function createTestLogger(): Log\Test\TestLogger
    {
        return new Log\Test\TestLogger();
    }
}
