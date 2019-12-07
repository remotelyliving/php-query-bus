<?php

declare(strict_types=1);

namespace RemotelyLiving\PHPQueryBus\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psr\Log\Test\TestLogger;

abstract class AbstractTestCase extends TestCase
{
    public function createTestLogger(): TestLogger
    {
        return new TestLogger();
    }
}
