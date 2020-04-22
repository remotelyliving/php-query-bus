<?php

declare(strict_types=1);

namespace RemotelyLiving\PHPQueryBus\Tests\Unit;

use RemotelyLiving\PHPQueryBus\Exceptions;
use RemotelyLiving\PHPQueryBus\Interfaces;
use RemotelyLiving\PHPQueryBus\Resolver;
use RemotelyLiving\PHPQueryBus\Tests\Stubs;

class ResolverTest extends AbstractTestCase
{
    private Stubs\Container $emptyContainer;

    private Stubs\Container $containerWithUserQuerySet;

    private Interfaces\Resolver $resolver;

    protected function setUp(): void
    {
        $this->emptyContainer = new Stubs\Container([]);
        $this->containerWithUserQuerySet = new Stubs\Container([
            Stubs\GetUserQuery::class => new Stubs\GetUserHandler(),
        ]);

        $this->resolver = Resolver::create();
    }

    public function testResolvesQueriesWhenFound(): void
    {
        $this->resolver->pushHandler(Stubs\GetUserQuery::class, new Stubs\GetUserHandler())
            ->pushHandler(Stubs\GetUserProfileQuery::class, new Stubs\GetUserProfileHandler());

        $this->assertInstanceOf(
            Stubs\GetUserHandler::class,
            $this->resolver->resolve(new Stubs\GetUserQuery('uuid'))
        );
        $this->assertInstanceOf(
            Stubs\GetUserProfileHandler::class,
            $this->resolver->resolve(new Stubs\GetUserProfileQuery('uuid'))
        );
    }

    public function testResolvesQueriesWhenFoundInContainer(): void
    {
        $this->resolver = Resolver::create($this->containerWithUserQuerySet);
        $this->resolver->pushHandler(Stubs\GetUserProfileQuery::class, new Stubs\GetUserProfileHandler());

        $this->assertInstanceOf(
            Stubs\GetUserHandler::class,
            $this->resolver->resolve(new Stubs\GetUserQuery('uuid'))
        );
        $this->assertInstanceOf(
            Stubs\GetUserProfileHandler::class,
            $this->resolver->resolve(new Stubs\GetUserProfileQuery('uuid'))
        );
    }

    public function testResolvesQueriesWhenHandlerDeferred(): void
    {
        $this->resolver->pushHandler(Stubs\GetUserQuery::class, new Stubs\GetUserHandler())
            ->pushHandlerDeferred(Stubs\GetUserProfileQuery::class, function () {
                return new Stubs\GetUserProfileHandler();
            });

        $this->assertInstanceOf(
            Stubs\GetUserHandler::class,
            $this->resolver->resolve(new Stubs\GetUserQuery('uuid'))
        );

        $this->assertInstanceOf(
            Stubs\GetUserProfileHandler::class,
            $this->resolver->resolve(new Stubs\GetUserProfileQuery('uuid'))
        );
    }

    public function testThrowsOutOfBoundsWhenNoHandlerFound(): void
    {
        $this->expectException(Exceptions\OutOfBounds::class);
        $this->resolver->resolve(new Stubs\GetUserQuery('uuid'));
    }

    public function testThrowsOutOfBoundsWhenNoHandlerFoundInContainer(): void
    {
        $this->resolver = Resolver::create($this->emptyContainer);

        $this->expectException(Exceptions\OutOfBounds::class);
        $this->resolver->resolve(new Stubs\GetUserQuery('uuid'));
    }
}
