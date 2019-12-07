<?php

declare(strict_types=1);

namespace RemotelyLiving\PHPQueryBus\Middleware;

use Psr\Cache\CacheItemPoolInterface;
use RemotelyLiving\PHPQueryBus\Interfaces\CacheableQuery;
use RemotelyLiving\PHPQueryBus\Interfaces\Query;
use RemotelyLiving\PHPQueryBus\Interfaces\Result;

final class QueryCacher
{
    /**
     * @var \Psr\Cache\CacheItemPoolInterface
     */
    private $cachePool;

    public function __construct(CacheItemPoolInterface $cachePool)
    {
        $this->cachePool = $cachePool;
    }

    public function __invoke(Query $query, callable $next): Result
    {
        if (!$query instanceof CacheableQuery) {
            return $next($query);
        }

        $cachedResult = $this->cachePool->getItem($query->getCacheKey());
        if ($cachedResult->isHit()) {
            return $cachedResult->get();
        }

        $cachedResult->set($next($query))
            ->expiresAfter($query->getTTL());

        $this->cachePool->save($cachedResult);

        return $cachedResult->get();
    }
}
