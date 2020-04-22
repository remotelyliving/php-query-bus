<?php

declare(strict_types=1);

namespace RemotelyLiving\PHPQueryBus\Middleware;

use Psr\Log;
use RemotelyLiving\PHPQueryBus\Interfaces;
use RemotelyLiving\PHPQueryBus\Traits;

final class QueryLogger implements Log\LoggerAwareInterface
{
    use Traits\Logger;

    public function __invoke(Interfaces\Query $query, callable $next): Interfaces\Result
    {
        if ($query instanceof Interfaces\LoggableQuery) {
            $this->getLogger()->log((string) $query->getLogLevel(), $query->getLogMessage(), $query->getLogContext());
        }

        return $next($query);
    }
}
