<?php

declare(strict_types=1);

namespace RemotelyLiving\PHPQueryBus\Tests\Unit;

use RemotelyLiving\PHPQueryBus\AbstractResult;
use RemotelyLiving\PHPQueryBus\Interfaces;
use RemotelyLiving\PHPQueryBus\QueryBus;

class QueryBusTest extends AbstractTestCase
{
    private Interfaces\Resolver $resolver;

    private QueryBus $queryBus;

    private Interfaces\Result $result;

    private Interfaces\Query $query;

    private Interfaces\Handler $handler;

    protected function setUp(): void
    {
        $this->resolver = $this->createMock(Interfaces\Resolver::class);
        $this->queryBus = QueryBus::create($this->resolver);
        $this->result = new class extends AbstractResult {
        };
        $this->query = new class implements Interfaces\Query {
        };
        $this->handler = new class ($this->result) implements Interfaces\Handler {
            private Interfaces\Result $expectedResult;

            public function __construct(Interfaces\Result $result)
            {
                $this->expectedResult = $result;
            }

            public function handle(Interfaces\Query $query, Interfaces\QueryBus $bus): Interfaces\Result
            {
                return $this->expectedResult;
            }
        };
    }

    public function testHandleQuery(): void
    {
        $this->resolver->method('resolve')
            ->with($this->query)
            ->willReturn($this->handler);

        $this->assertSame($this->result, $this->queryBus->handle($this->query));
        $this->assertFalse($this->queryBus->handle($this->query)->isNotFound());
    }

    public function testPushesMiddlewareLIFO(): void
    {
        $this->resolver->method('resolve')
            ->with($this->query)
            ->willReturn($this->handler);

        $calledMiddleware = [];

        $middleware1 = function (Interfaces\Query $query, callable $next) use (&$calledMiddleware) {
            $calledMiddleware[] = 'middleware1';
            return $next($query);
        };

        $middleware2 = function (Interfaces\Query $query, callable $next) use (&$calledMiddleware) {
            $calledMiddleware[] = 'middleware2';
            return $next($query);
        };

        $middleware3 = function (Interfaces\Query $query, callable $next) use (&$calledMiddleware) {
            $calledMiddleware[] = 'middleware3';
            return $next($query);
        };

        $this->queryBus->pushMiddleware($middleware2)
            ->pushMiddleware($middleware3)
            ->pushMiddleware($middleware1);

        $this->assertSame($this->result, $this->queryBus->handle($this->query));
        $this->assertSame(['middleware1', 'middleware3', 'middleware2'], $calledMiddleware);
    }
}
