<?php

declare(strict_types=1);

namespace RemotelyLiving\PHPQueryBus\Middleware;

use Psr\Cache as PSRCache;
use RemotelyLiving\PHPQueryBus\DTO;
use RemotelyLiving\PHPQueryBus\Interfaces;

final class QueryCacher
{
    private PSRCache\CacheItemPoolInterface $cachePool;

    public function __construct(PSRCache\CacheItemPoolInterface $cachePool)
    {
        $this->cachePool = $cachePool;
    }

    public function __invoke(object $query, callable $next): Interfaces\Result
    {
        if (!$query instanceof Interfaces\CacheableQuery) {
            return $next($query);
        }

        $cachedResult = $this->cachePool->getItem($query->getCacheKey());

        if ($query->shouldRecomputeResult() || $this->shouldRecomputeResult($cachedResult)) {
            return $this->recomputeResult($cachedResult, $query, $next);
        }

        /** @var DTO\Cache\ResultWrapper $resultWrapper */
        $resultWrapper = $cachedResult->get();

        return $resultWrapper->getResult();
    }

    private function shouldRecomputeResult(PSRCache\CacheItemInterface $cacheItem): bool
    {
        if (!$cacheItem->isHit()) {
            return true;
        }

        if (!is_object($cacheItem->get()) || ($cacheItem->get() instanceof DTO\Cache\ResultWrapper) === false) {
            return true;
        }

        /** @var DTO\Cache\ResultWrapper $wrapper */
        $wrapper = $cacheItem->get();
        return $wrapper->willProbablyExpireSoon();
    }

    private function recomputeResult(
        PSRCache\CacheItemInterface $cacheItem,
        Interfaces\CacheableQuery $query,
        callable $next
    ): Interfaces\Result {
        $startTime = microtime(true);
        /** @var Interfaces\Result $result */
        $result = $next($query);
        $stopTime = microtime(true);
        $computeTime = (int) ($stopTime - $startTime);
        $recomputed = $cacheItem->set(DTO\Cache\ResultWrapper::wrap($result, $computeTime, $query->getTTL()))
          ->expiresAfter($query->getTTL());

        $this->cachePool->save($recomputed);

        return $result;
    }
}
