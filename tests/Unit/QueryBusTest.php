<?php

declare(strict_types=1);

namespace RemotelyLiving\PHPQueryBus\Tests\Unit;

use RemotelyLiving\PHPQueryBus;
use RemotelyLiving\PHPQueryBus\Interfaces;
use RemotelyLiving\PHPQueryBus\QueryBus;

class QueryBusTest extends AbstractTestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\RemotelyLiving\PHPQueryBus\Interfaces\Resolver
     */
    private $resolver;

    /**
     * @var \RemotelyLiving\PHPQueryBus\QueryBus
     */
    private $queryBus;

    /**
     * @var \RemotelyLiving\PHPQueryBus\Interfaces\Result
     */
    private $result;

    /**
     * @var \RemotelyLiving\PHPQueryBus\Interfaces\Query
     */
    private $query;

    /**
     * @var \RemotelyLiving\PHPQueryBus\Interfaces\Handler
     */
    private $handler;

    protected function setUp(): void
    {
        $this->resolver = $this->createMock(Interfaces\Resolver::class);
        $this->queryBus = QueryBus::create($this->resolver);
        $this->result = new class implements Interfaces\Result {
            public function jsonSerialize()
            {
                ['foo' => 'bar'];
            }
        };

        $this->query = new class implements Interfaces\Query {
        };
        $this->handler = new class ($this->result) implements Interfaces\Handler {
            private $expectedResult;

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
