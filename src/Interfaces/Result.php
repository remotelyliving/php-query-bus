<?php

declare(strict_types=1);

namespace RemotelyLiving\PHPQueryBus\Interfaces;

interface Result
{
    public function isNotFound(): bool;

    public function hasErrors(): bool;

    /**@return \Throwable[] */
    public function getErrors(): iterable;
}
