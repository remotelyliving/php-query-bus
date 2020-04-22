<?php

declare(strict_types=1);

namespace RemotelyLiving\PHPQueryBus\Tests\Integration;

use Psr\Cache\CacheItemPoolInterface;
use RemotelyLiving\PHPQueryBus\DTO;
use RemotelyLiving\PHPQueryBus\Enums;
use RemotelyLiving\PHPQueryBus\Middleware;
use RemotelyLiving\PHPQueryBus\QueryBus;
use RemotelyLiving\PHPQueryBus\Tests\Stubs;

class MiddlewareTest extends AbstractTestCase
{

    private QueryBus $queryBus;

    private CacheItemPoolInterface $cacheItemPool;

    protected function setUp(): void
    {

        $this->queryBus = $this->createConfiguredQueryBus();
        $this->cacheItemPool = $this->getCacheItemPool();
        $this->cacheItemPool->clear();
    }

    protected function tearDown(): void
    {

        $this->cacheItemPool->clear();
        parent::tearDown();
    }

    public function testLoggerMiddlewareLogsLoggableQueries(): void
    {

        $query = new Stubs\GetUserProfileQuery('uuid');
        $this->queryBus->handle($query);

        $this->assertEquals(
            [
                'level' => Stubs\GetUserProfileQuery::LOG_LEVEL,
                'message' => Stubs\GetUserProfileQuery::LOG_MESSAGE,
                'context' => Stubs\GetUserProfileQuery::LOG_CONTEXT,
            ],
            $this->getTestLogger()->records[0]
        );
    }

    public function testErrorLoggerMiddlewareDoeNotLogErrorFreeResults(): void
    {

        $query = new Stubs\GetUserProfileQuery('uuid');
        $this->queryBus->handle($query);
        $this->assertFalse($this->getTestLogger()->hasErrorRecords());
    }

    public function testErrorLoggerMiddlewareDoeNotLogResultsWithNoError(): void
    {

        $query = new Stubs\GetUserProfileQuery('uuid');
        $errorResult = $this->queryBus->handle($query->handlerShouldReturnWithErrors());
        $error1 = $errorResult->getErrors()[0];
        $error2 = $errorResult->getErrors()[1];

        $this->assertEquals(
            [
                'level' => 'error',
                'message' => 'Query bus result fetched with errors',
                'context' => [
                    [
                        'ExceptionClass' => \LogicException::class,
                        'Message' => $error1->getMessage(),
                        'Line' => $error1->getLine(),
                        'File' => $error1->getFile(),
                        'Trace' => $error1->getTraceAsString(),
                    ],
                    [
                        'ExceptionClass' => \InvalidArgumentException::class,
                        'Message' => $error2->getMessage(),
                        'Line' => $error2->getLine(),
                        'File' => $error2->getFile(),
                        'Trace' => $error2->getTraceAsString(),
                    ],
                ],
            ],
            $this->getTestLogger()->records[1]
        );
    }

    public function testLoggerMiddlewareDoesNotLogNonLoggableQueries(): void
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
            Stubs\GetUserQuery::class,
            $this->getTestLogger()->records[0]['context']['query']
        );
    }

    public function testPerfBudgetMiddlewareLogsPerfIssuesAtDesiredLevel(): void
    {

        $perfMiddleware = new Middleware\PerfBudgetLogger(0, 0, Enums\LogLevel::DEBUG());
        $perfMiddleware->setLogger($this->getTestLogger());
        $this->queryBus->pushMiddleware($perfMiddleware);

        $query = new Stubs\GetUserQuery('uuid');
        $this->queryBus->handle($query);

        $this->assertSame('debug', $this->getTestLogger()->records[0]['level']);
        $this->assertSame('Performance threshold exceeded', $this->getTestLogger()->records[0]['message']);
        $this->assertSame(
            Stubs\GetUserQuery::class,
            $this->getTestLogger()->records[0]['context']['query']
        );
    }

    public function testQueryCacherCachesResults(): void
    {

        $this->queryBus->pushMiddleware(new Middleware\QueryLogger($this->getTestLogger()))
            ->pushMiddleware(new Middleware\QueryCacher($this->cacheItemPool));
        $query = new Stubs\GetUserProfileQuery('the-user-id');
        $this->assertFalse($this->cacheItemPool->hasItem($query->getCacheKey()));

        $result = $this->queryBus->handle($query);

        $this->assertTrue($this->cacheItemPool->hasItem($query->getCacheKey()));

        /** @var DTO\Cache\ResultWrapper $resultWrapper */
        $resultWrapper = $this->cacheItemPool->getItem($query->getCacheKey())->get();

        $this->assertInstanceOf(DTO\Cache\ResultWrapper::class, $resultWrapper);
        $this->assertInstanceOf(Stubs\GetUserProfileResult::class, $resultWrapper->getResult());
        $this->assertEquals($result, $resultWrapper->getResult());
    }
}
