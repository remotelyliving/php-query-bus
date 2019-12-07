<?php

declare(strict_types=1);

namespace RemotelyLiving\PHPQueryBus;

use RemotelyLiving\PHPQueryBus\Interfaces;

final class QueryBus implements Interfaces\QueryBus
{
    /**
     * @var \RemotelyLiving\PHPQueryBus\Interfaces\Resolver
     */
    private $resolver;

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

        $this->callStack = function (Interfaces\Query $query) use ($middleware, $next): Interfaces\Result {
            return $middleware($query, $next);
        };

        return $this;
    }

    public function handle(Interfaces\Query $query): Interfaces\Result
    {
        return ($this->callStack)($query);
    }

    private function seedCallStack(): callable
    {
        return function (Interfaces\Query $query): Interfaces\Result {
            return $this->resolver->resolve($query)->handle($query, $this);
        };
    }
}
