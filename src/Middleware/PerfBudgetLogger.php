<?php

declare(strict_types=1);

namespace RemotelyLiving\PHPQueryBus\Middleware;

use RemotelyLiving\PHPQueryBus\Interfaces\Query;
use RemotelyLiving\PHPQueryBus\Interfaces\Result;
use RemotelyLiving\PHPQueryBus\Enums\LogLevel;
use RemotelyLiving\PHPQueryBus\Traits\Logger;

final class PerfBudgetLogger implements \Psr\Log\LoggerAwareInterface
{
    use Logger;

    /**
     * @var int
     */
    private $thresholdSeconds;

    /**
     * @var float
     */
    private $thresholdMemoryMB;

    /**
     * @var \RemotelyLiving\PHPQueryBus\Enums\LogLevel
     */
    private $logLevel;

    public function __construct(int $thresholdSeconds = 10, float $thresholdMemoryMB = 10.0, LogLevel $logLevel = null)
    {
        $this->thresholdSeconds = $thresholdSeconds;
        $this->thresholdMemoryMB = $thresholdMemoryMB;
        $this->logLevel = $logLevel ?? LogLevel::WARNING();
    }

    public function __invoke(Query $query, callable $next): Result
    {
        $startTime = microtime(true);
        $startMemoryBytes = memory_get_usage(true);
        $result = $next($query);
        $totalTime = (float) microtime(true) - $startTime;
        $totalMegabytesIncrease = (memory_get_peak_usage(true) - $startMemoryBytes) / 1000000;

        if ($this->isOverThresholds($totalTime, $totalMegabytesIncrease)) {
            $this->getLogger()->log(
                (string) $this->logLevel,
                'Performance threshold exceeded',
                ['MB' => $totalMegabytesIncrease, 'seconds' => $totalTime, 'query' => get_class($query) ]
            );
        }

        return $result;
    }

    private function isOverThresholds(float $seconds, float $megaBytes): bool
    {
        return ($seconds > $this->thresholdSeconds || ($megaBytes) > $this->thresholdMemoryMB);
    }
}
