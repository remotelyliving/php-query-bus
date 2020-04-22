<?php

declare(strict_types=1);

namespace RemotelyLiving\PHPQueryBus\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\Test\TestLogger;
use RemotelyLiving\PHPQueryBus\Middleware;
use RemotelyLiving\PHPQueryBus\QueryBus;
use RemotelyLiving\PHPQueryBus\Resolver;
use RemotelyLiving\PHPQueryBus\Tests\Stubs;
use Symfony\Component\Cache as SymfonyCache;

abstract class AbstractTestCase extends TestCase
{
    protected ?TestLogger $testLogger = null;

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

        $resultErrorLogger = new Middleware\ResultErrorLogger();
        $resultErrorLogger->setLogger($this->getTestLogger());

        $container = new Stubs\Container([
            Stubs\GetUserProfileQuery::class => new Stubs\GetUserProfileHandler(),
        ]);

        $resolver = Resolver::create($container);
        $lazyFn = function (): Stubs\GetUserHandler {
            return new Stubs\GetUserHandler();
        };

        $resolver->pushHandlerDeferred(Stubs\GetUserQuery::class, $lazyFn);

        return QueryBus::create($resolver)
            ->pushMiddleware($queryLogger)
            ->pushMiddleware($resultErrorLogger);
    }

    public function getCacheItemPool(): CacheItemPoolInterface
    {
        return new SymfonyCache\Adapter\ArrayAdapter(300, true);
    }
}
