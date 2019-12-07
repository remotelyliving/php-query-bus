<?php

declare(strict_types=1);

namespace RemotelyLiving\PHPQueryBus\Tests\Unit\Middleware;

use RemotelyLiving\PHPQueryBus\Interfaces\Query;
use RemotelyLiving\PHPQueryBus\Interfaces\Result;
use RemotelyLiving\PHPQueryBus\Middleware\QueryLogger;
use RemotelyLiving\PHPQueryBus\Tests\Stubs\GetUserProfileQuery;
use RemotelyLiving\PHPQueryBus\Tests\Stubs\GetUserQuery;
use RemotelyLiving\PHPQueryBus\Tests\Unit\AbstractTestCase;

class QueryLoggerTest extends AbstractTestCase
{
    /**
     * @var \Psr\Log\Test\TestLogger
     */
    private $testLogger;

    /**
     * @var \RemotelyLiving\PHPQueryBus\Interfaces\LoggableQuery
     */
    private $loggableQuery;

    /**
     * @var \RemotelyLiving\PHPQueryBus\Interfaces\Query
     */
    private $nonLoggableQuery;

    /**
     * @var \RemotelyLiving\PHPQueryBus\Interfaces\Result
     */
    private $result;

    /**
     * @var callable
     */
    private $next;

    /**
     * @var callable
     */
    private $queryLogger;

    protected function setUp(): void
    {
        $this->testLogger = $this->createTestLogger();
        $this->loggableQuery = new GetUserProfileQuery('uuid');
        $this->nonLoggableQuery = new GetUserQuery('uuid');
        $this->result = new class implements Result {
            public function jsonSerialize()
            {
                return [];
            }
        };

        $this->next = function (Query $query): Result {
            return $this->result;
        };

        $this->queryLogger = new QueryLogger();
        $this->queryLogger->setLogger($this->testLogger);
    }

    public function testDoesNotLogNonLoggableQueries(): void
    {
        $result = ($this->queryLogger)($this->nonLoggableQuery, $this->next);
        $this->assertInstanceOf(Result::class, $result);
        $this->assertEmpty($this->testLogger->records);
    }

    public function testLogsLoggableQueries(): void
    {
        $result = ($this->queryLogger)($this->loggableQuery, $this->next);
        $this->assertInstanceOf(Result::class, $result);
        $this->assertSame([
            'level' => GetUserProfileQuery::LOG_LEVEL,
            'message' => GetUserProfileQuery::LOG_MESSAGE,
            'context' => GetUserProfileQuery::LOG_CONTEXT,
        ], $this->testLogger->records[0]);
    }
}
