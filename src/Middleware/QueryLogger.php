<?php

declare(strict_types=1);

namespace RemotelyLiving\PHPQueryBus\Middleware;

use RemotelyLiving\PHPQueryBus\Interfaces\LoggableQuery;
use RemotelyLiving\PHPQueryBus\Interfaces\Query;
use RemotelyLiving\PHPQueryBus\Interfaces\Result;
use RemotelyLiving\PHPQueryBus\Traits\Logger;

final class QueryLogger implements \Psr\Log\LoggerAwareInterface
{
    use Logger;

    public function __invoke(Query $query, callable $next): Result
    {
        if ($query instanceof LoggableQuery) {
            $this->getLogger()->log((string) $query->getLogLevel(), $query->getLogMessage(), $query->getLogContext());
        }

        return $next($query);
    }
}
