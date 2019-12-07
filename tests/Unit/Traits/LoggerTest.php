<?php

declare(strict_types=1);

namespace RemotelyLiving\PHPQueryBus\Tests\Traits\Unit;

use Psr\Log;
use RemotelyLiving\PHPQueryBus\Tests\Unit\AbstractTestCase;
use RemotelyLiving\PHPQueryBus\Traits;

class LoggerTest extends AbstractTestCase
{
    /**
     * @var \Psr\Log\Test\TestLogger
     */
    private $testLogger;

    /**
     * @var \Psr\Log\LoggerAwareInterface
     */
    private $loggableClass;

    protected function setUp(): void
    {
        $this->testLogger = $this->createTestLogger();
        $this->loggableClass = new class implements Log\LoggerAwareInterface {
            use Traits\Logger;

            public function getTraitLogger(): Log\LoggerInterface
            {
                return $this->getLogger();
            }
        };
    }

    public function testDefaultLoggerIsNullLogger(): void
    {
        $this->assertInstanceOf(Log\NullLogger::class, $this->loggableClass->getTraitLogger());
    }

    public function testSetsLogger(): void
    {
        $this->loggableClass->setLogger($this->testLogger);
        $this->assertInstanceOf(Log\Test\TestLogger::class, $this->loggableClass->getTraitLogger());
    }

    public function testLogs(): void
    {
        $this->loggableClass->setLogger($this->testLogger);
        $this->loggableClass->getTraitLogger()->log('critical', 'the message', ['the' => 'context']);

        $this->assertSame([
            'level' => 'critical',
            'message' => 'the message',
            'context' => ['the' => 'context'],
        ], $this->testLogger->records[0]);
    }
}
