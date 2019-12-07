<?php

declare(strict_types=1);

namespace RemotelyLiving\PHPQueryBus\Tests\Unit\Middleware;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use RemotelyLiving\PHPQueryBus\Interfaces\Query;
use RemotelyLiving\PHPQueryBus\Interfaces\Result;
use RemotelyLiving\PHPQueryBus\Middleware\QueryCacher;
use RemotelyLiving\PHPQueryBus\Tests\Stubs\GetUserProfileQuery;
use RemotelyLiving\PHPQueryBus\Tests\Stubs\GetUserQuery;
use RemotelyLiving\PHPQueryBus\Tests\Unit\AbstractTestCase;

class QueryCacherTest extends AbstractTestCase
{
    /**
     * @var \Psr\Cache\CacheItemPoolInterface
     */
    private $cachePool;

    /**
     * @var \Psr\Cache\CacheItemInterface
     */
    private $cacheItem;

    /**
     * @var \RemotelyLiving\PHPQueryBus\Interfaces\CacheableQuery
     */
    private $cacheableQuery;

    /**
     * @var \RemotelyLiving\PHPQueryBus\Interfaces\Query
     */
    private $nonCacheableQuery;

    /**
     * @var \RemotelyLiving\PHPQueryBus\Interfaces\Result
     */
    private $result;

    /**
     * @var callable
     */
    private $next;

    /**
     * @var callable
     */
    private $queryCacher;

    protected function setUp(): void
    {
        $this->cachePool = $this->createMock(CacheItemPoolInterface::class);
        $this->cacheItem = $this->createMock(CacheItemInterface::class);

        $this->cacheableQuery = new GetUserProfileQuery('uuid');
        $this->nonCacheableQuery = new GetUserQuery('uuid');
        $this->result = new class implements Result {
            public function jsonSerialize(): array
            {
                return [];
            }
        };

        $this->next = function (Query $query): Result {
            return $this->result;
        };

        $this->queryCacher = new QueryCacher($this->cachePool);
    }

    public function testDoesNotCacheNonCacheableeQueries(): void
    {
        $this->cachePool->expects($this->never())
            ->method('getItem');

        $result = ($this->queryCacher)($this->nonCacheableQuery, $this->next);
        $this->assertInstanceOf(Result::class, $result);
    }

    public function testDoesGetCacheableQueriesFromCache(): void
    {
        $this->cachePool->method('getItem')
            ->with(GetUserProfileQuery::CACHE_KEY)
            ->willReturn($this->cacheItem);

        $this->cacheItem->method('isHit')
            ->willReturn(true);

        $this->cacheItem->method('get')
            ->willReturn($this->result);

        $result = ($this->queryCacher)($this->cacheableQuery, $this->next);
        $this->assertInstanceOf(Result::class, $result);
    }

    public function testDoesGetCacheableQueriesFromCacheAndPopulatesCacheIfMissing(): void
    {
        $this->cachePool->method('getItem')
            ->with(GetUserProfileQuery::CACHE_KEY)
            ->willReturn($this->cacheItem);

        $this->cachePool->expects($this->once())
            ->method('save')
            ->with($this->cacheItem);

        $this->cacheItem->method('isHit')
            ->willReturn(false);

        $this->cacheItem->method('set')
            ->with($this->result)
            ->willReturn($this->cacheItem);

        $this->cacheItem->method('expiresAfter')
            ->with(GetUserProfileQuery::CACHE_TTL)
            ->willReturn($this->cacheItem);

        $this->cacheItem->method('get')
            ->willReturn($this->result);

        $result = ($this->queryCacher)($this->cacheableQuery, $this->next);
        $this->assertInstanceOf(Result::class, $result);
    }
}
