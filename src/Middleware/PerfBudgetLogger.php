<?php

declare(strict_types=1);

namespace RemotelyLiving\PHPQueryBus\Middleware;

use Psr\Log;
use RemotelyLiving\PHPQueryBus\Interfaces;
use RemotelyLiving\PHPQueryBus\Enums;
use RemotelyLiving\PHPQueryBus\Traits;

final class PerfBudgetLogger implements Log\LoggerAwareInterface
{
    use Traits\Logger;

    private int $thresholdSeconds;

    private float $thresholdMemoryMB;

    private Enums\LogLevel $logLevel;

    public function __construct(
        int $thresholdSeconds = 10,
        float $thresholdMemoryMB = 10.0,
        Enums\LogLevel $logLevel = null
    ) {
        $this->thresholdSeconds = $thresholdSeconds;
        $this->thresholdMemoryMB = $thresholdMemoryMB;
        $this->logLevel = $logLevel ?? Enums\LogLevel::WARNING();
    }

    public function __invoke(object $query, callable $next): Interfaces\Result
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
