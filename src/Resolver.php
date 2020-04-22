<?php

declare(strict_types=1);

namespace RemotelyLiving\PHPQueryBus;

use Psr\Container\ContainerInterface;
use RemotelyLiving\PHPQueryBus\Exceptions;
use RemotelyLiving\PHPQueryBus\Interfaces;

final class Resolver implements Interfaces\Resolver
{
    private ?ContainerInterface $container;

    /**
     * @var \RemotelyLiving\PHPQueryBus\Interfaces\Handler[]
     */
    private array $map = [];

    /**
     * @var callable[]
     */
    private array $deferred = [];

    public function __construct(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public static function create(ContainerInterface $container = null): Interfaces\Resolver
    {
        return new static($container);
    }

    public function resolve(object $query): Interfaces\Handler
    {
        $queryClass = get_class($query);

        if ($this->container && $this->container->has($queryClass)) {
            return $this->container->get($queryClass);
        }

        if (isset($this->map[$queryClass])) {
            return $this->map[$queryClass];
        }

        if (isset($this->deferred[$queryClass])) {
            $this->map[$queryClass] = $this->deferred[$queryClass]();
            unset($this->deferred[$queryClass]);
            return $this->map[$queryClass];
        }

        throw new Exceptions\OutOfBounds("Query Handler for {$queryClass} not found");
    }

    public function pushHandler(string $queryClass, Interfaces\Handler $handler): Interfaces\Resolver
    {
        $this->map[$queryClass] = $handler;

        return $this;
    }

    public function pushHandlerDeferred(string $class, callable $handlerFn): Interfaces\Resolver
    {
        $this->deferred[$class] = $handlerFn;

        return $this;
    }
}
