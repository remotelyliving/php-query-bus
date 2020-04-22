<?php

declare(strict_types=1);

namespace RemotelyLiving\PHPQueryBus\Tests\Stubs;

use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class Container implements ContainerInterface
{
    private array $services;

    public function __construct(array $services = [])
    {
        $this->services = $services;
    }

    public function get($id)
    {
        if ($this->has($id)) {
            return $this->services[$id];
        }

        throw new class ('Not found') extends \Exception implements NotFoundExceptionInterface {
        };
    }

    public function has($id): bool
    {
        return isset($this->services[$id]);
    }
}
