<?php

declare(strict_types=1);

namespace RemotelyLiving\PHPQueryBus;

use RemotelyLiving\PHPQueryBus\Interfaces;

abstract class AbstractResult implements Interfaces\Result
{
    final public function isNotFound(): bool
    {
        return false;
    }

    final public function getErrors(): iterable
    {
        return [];
    }

    final public function hasErrors(): bool
    {
        return false;
    }

    final public static function notFound(): Interfaces\Result
    {
        return new class implements Interfaces\Result
        {
            public function isNotFound(): bool
            {
                return true;
            }

            public function getErrors(): iterable
            {
                return [];
            }

            public function hasErrors(): bool
            {
                return false;
            }
        };
    }

    final public static function withErrors(\Throwable ...$errors): Interfaces\Result
    {
        return new class (...$errors) implements Interfaces\Result
        {
            private array $errors = [];

            public function __construct(\Throwable ...$errors)
            {
                $this->errors = $errors;
            }

            public function isNotFound(): bool
            {
                return false;
            }

            public function getErrors(): iterable
            {
                return $this->errors;
            }

            public function hasErrors(): bool
            {
                return true;
            }
        };
    }
}
