<?php

declare(strict_types=1);

namespace RemotelyLiving\PHPQueryBus;

use RemotelyLiving\PHPQueryBus\Interfaces;

final class QueryBus implements Interfaces\QueryBus
{
    private Interfaces\Resolver $resolver;

    /**
     * @var callable
     */
    private $callStack;

    public function __construct(Interfaces\Resolver $resolver)
    {
        $this->resolver = $resolver;
        $this->callStack = $this->seedCallStack();
    }

    public static function create(Interfaces\Resolver $resolver): self
    {
        return new static($resolver);
    }

    public function pushMiddleware(callable $middleware): Interfaces\QueryBus
    {
        $next = $this->callStack;

        $this->callStack = function (object $query) use ($middleware, $next): Interfaces\Result {
            return $middleware($query, $next);
        };

        return $this;
    }

    public function handle(object $query): Interfaces\Result
    {
        return ($this->callStack)($query);
    }

    private function seedCallStack(): callable
    {
        return function (object $query): Interfaces\Result {
            return $this->resolver->resolve($query)->handle($query, $this);
        };
    }
}
