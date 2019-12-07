<?php

declare(strict_types=1);

namespace RemotelyLiving\PHPQueryBus\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Psr\Log\Test\TestLogger;
use RemotelyLiving\PHPQueryBus\Middleware;
use RemotelyLiving\PHPQueryBus\QueryBus;
use RemotelyLiving\PHPQueryBus\Resolver;
use RemotelyLiving\PHPQueryBus\Tests\Stubs;

abstract class AbstractTestCase extends TestCase
{
    protected $testLogger = null;

    public function getTestLogger(): TestLogger
    {
        if ($this->testLogger === null) {
            $this->testLogger = new TestLogger();
        }

        return $this->testLogger;
    }

    public function createConfiguredQueryBus(): QueryBus
    {
        $queryLogger = new Middleware\QueryLogger();
        $queryLogger->setLogger($this->getTestLogger());

        $container = new Stubs\Container([
            Stubs\GetUserProfileQuery::class => new Stubs\GetUserProfileHandler(),
        ]);

        $resolver = Resolver::create($container);
        $lazyFn = function (): Stubs\GetUserHandler {
            return new Stubs\GetUserHandler();
        };
        $resolver->pushHandlerDeferred(Stubs\GetUserQuery::class, $lazyFn);

        return QueryBus::create($resolver)
            ->pushMiddleware($queryLogger);
    }
}
