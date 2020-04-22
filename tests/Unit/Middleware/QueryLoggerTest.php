<?php

declare(strict_types=1);

namespace RemotelyLiving\PHPQueryBus\Tests\Unit\Middleware;

use Psr\Log\Test\TestLogger;
use RemotelyLiving\PHPQueryBus\AbstractResult;
use RemotelyLiving\PHPQueryBus\Interfaces;
use RemotelyLiving\PHPQueryBus\Middleware;
use RemotelyLiving\PHPQueryBus\Tests\Stubs;
use RemotelyLiving\PHPQueryBus\Tests\Unit\AbstractTestCase;

class QueryLoggerTest extends AbstractTestCase
{
    private TestLogger $testLogger;

    private Interfaces\LoggableQuery $loggableQuery;

    private object $nonLoggableQuery;

    private Interfaces\Result $result;

    private \Closure $next;

    private Middleware\QueryLogger $queryLogger;

    protected function setUp(): void
    {
        $this->testLogger = $this->createTestLogger();
        $this->loggableQuery = new Stubs\GetUserProfileQuery('uuid');
        $this->nonLoggableQuery = new Stubs\GetUserQuery('uuid');
        $this->result = new class extends AbstractResult {
            public function toArray(): array
            {
                return [];
            }
        };

        $this->next = function (): Interfaces\Result {
            return $this->result;
        };

        $this->queryLogger = new Middleware\QueryLogger();
        $this->queryLogger->setLogger($this->testLogger);
    }

    public function testDoesNotLogNonLoggableQueries(): void
    {
        $result = ($this->queryLogger)($this->nonLoggableQuery, $this->next);
        $this->assertEquals($this->result, $result);
        $this->assertEmpty($this->testLogger->records);
    }

    public function testLogsLoggableQueries(): void
    {
        $result = ($this->queryLogger)($this->loggableQuery, $this->next);
        $this->assertEquals($this->result, $result);
        $this->assertSame([
            'level' => Stubs\GetUserProfileQuery::LOG_LEVEL,
            'message' => Stubs\GetUserProfileQuery::LOG_MESSAGE,
            'context' => Stubs\GetUserProfileQuery::LOG_CONTEXT,
        ], $this->testLogger->records[0]);
    }
}
