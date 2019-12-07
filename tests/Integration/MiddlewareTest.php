<?php

declare(strict_types=1);

namespace RemotelyLiving\PHPQueryBus\Tests\Integration;

use RemotelyLiving\PHPQueryBus\Enums\LogLevel;
use RemotelyLiving\PHPQueryBus\Middleware;
use RemotelyLiving\PHPQueryBus\Tests\Stubs;

class MiddlewareTest extends AbstractTestCase
{
    /**
     * @var \RemotelyLiving\PHPQueryBus\QueryBus
     */
    private $queryBus;

    protected function setUp(): void
    {
        $this->queryBus = $this->createConfiguredQueryBus();
    }

    public function testLogsLoggableQueriesInMiddleware(): void
    {
        $query = new Stubs\GetUserProfileQuery('uuid');
        $this->queryBus->handle($query);

        $this->assertEquals([
            'level' => Stubs\GetUserProfileQuery::LOG_LEVEL,
            'message' => Stubs\GetUserProfileQuery::LOG_MESSAGE,
            'context' => Stubs\GetUserProfileQuery::LOG_CONTEXT,
        ], $this->getTestLogger()->records[0]);
    }

    public function testDoesNotLogNonLoggableQueries(): void
    {
        $query = new Stubs\GetUserQuery('uuid');
        $this->queryBus->handle($query);

        $this->assertEmpty($this->getTestLogger()->records);
    }

    public function testPerfBudgetMiddlewareLogsPerfIssues(): void
    {
        $perfMiddleware = new Middleware\PerfBudgetLogger(0, 0);
        $perfMiddleware->setLogger($this->getTestLogger());
        $this->queryBus->pushMiddleware($perfMiddleware);

        $query = new Stubs\GetUserQuery('uuid');
        $this->queryBus->handle($query);

        $this->assertSame('warning', $this->getTestLogger()->records[0]['level']);
        $this->assertSame('Performance threshold exceeded', $this->getTestLogger()->records[0]['message']);
        $this->assertSame(
            'RemotelyLiving\PHPQueryBus\Tests\Stubs\GetUserQuery',
            $this->getTestLogger()->records[0]['context']['query']
        );
    }

    public function testPerfBudgetMiddlewareLogsPerfIssuesAtDesiredLevel(): void
    {
        $perfMiddleware = new Middleware\PerfBudgetLogger(0, 0, LogLevel::DEBUG());
        $perfMiddleware->setLogger($this->getTestLogger());
        $this->queryBus->pushMiddleware($perfMiddleware);

        $query = new Stubs\GetUserQuery('uuid');
        $this->queryBus->handle($query);

        $this->assertSame('debug', $this->getTestLogger()->records[0]['level']);
        $this->assertSame('Performance threshold exceeded', $this->getTestLogger()->records[0]['message']);
        $this->assertSame(
            'RemotelyLiving\PHPQueryBus\Tests\Stubs\GetUserQuery',
            $this->getTestLogger()->records[0]['context']['query']
        );
    }
}
