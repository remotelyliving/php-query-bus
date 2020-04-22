<?php

declare(strict_types=1);

namespace RemotelyLiving\PHPQueryBus\Middleware;

use Psr\Log;
use RemotelyLiving\PHPQueryBus\Interfaces;
use RemotelyLiving\PHPQueryBus\Traits;

final class ResultErrorLogger implements Log\LoggerAwareInterface
{
    use Traits\Logger;

    public function __invoke(Interfaces\Query $query, callable $next): Interfaces\Result
    {
        /** @var \RemotelyLiving\PHPQueryBus\Interfaces\Result $result */
        $result = $next($query);

        if ($result->hasErrors()) {
            $this->getLogger()->error('Query bus result fetched with errors', $this->formatErrorContext($result));
        }

        return $result;
    }

    private function formatErrorContext(Interfaces\Result $result): array
    {
        $errorContext = [];
        foreach ($result->getErrors() as $error) {
            $errorContext[] = [
                'ExceptionClass' => get_class($error),
                'Message' => $error->getMessage(),
                'Line' => $error->getLine(),
                'File' => $error->getFile(),
                'Trace' => $error->getTraceAsString(),
            ];
        }

        return $errorContext;
    }
}
