<?php

declare(strict_types=1);

namespace RemotelyLiving\PHPQueryBus\Tests\Unit\Middleware;

use Psr\Cache as PSRCache;
use RemotelyLiving\PHPQueryBus\AbstractResult;
use RemotelyLiving\PHPQueryBus\DTO;
use RemotelyLiving\PHPQueryBus\Interfaces;
use RemotelyLiving\PHPQueryBus\Middleware;
use RemotelyLiving\PHPQueryBus\Tests\Stubs;
use RemotelyLiving\PHPQueryBus\Tests\Unit\AbstractTestCase;

class QueryCacherTest extends AbstractTestCase
{
    private PSRCache\CacheItemPoolInterface $cachePool;

    private PSRCache\CacheItemInterface $cacheItem;

    private Interfaces\CacheableQuery $cacheableQuery;

    private Interfaces\Query $nonCacheableQuery;

    private Interfaces\Result $result;

    private DTO\Cache\ResultWrapper $resultWrapper;

    private \Closure $next;

    private Middleware\QueryCacher $queryCacher;

    protected function setUp(): void
    {
        $this->cachePool = $this->createMock(PSRCache\CacheItemPoolInterface::class);
        $this->cacheItem = $this->createMock(PSRCache\CacheItemInterface::class);
        $this->cacheableQuery = new Stubs\GetUserProfileQuery('uuid');
        $this->nonCacheableQuery = new Stubs\GetUserQuery('uuid');
        $this->result = new class extends AbstractResult {
            public function toArray(): array
            {
                return [];
            }
        };

        $this->next = function (): Interfaces\Result {
            return $this->result;
        };

        $this->resultWrapper = DTO\Cache\ResultWrapper::wrap($this->result, 10, 300);
        $this->queryCacher = new Middleware\QueryCacher($this->cachePool);
    }

    public function testDoesNotCacheNonCacheableQueries(): void
    {
        $this->cachePool->expects($this->never())
            ->method('getItem');

        $result = ($this->queryCacher)($this->nonCacheableQuery, $this->next);

        $this->assertEquals($this->result, $result);
    }

    public function testDoesGetCacheableQueriesAlreadyInCache(): void
    {
        $this->cachePool->method('getItem')
            ->with(Stubs\GetUserProfileQuery::CACHE_KEY)
            ->willReturn($this->cacheItem);

        $this->cachePool->method('save');

        $this->cacheItem->method('isHit')
            ->willReturn(true);

        $this->cacheItem->method('get')
            ->willReturn($this->resultWrapper);

        $result = ($this->queryCacher)($this->cacheableQuery, $this->next);

        $this->assertEquals($this->result, $result);
    }

    public function testDoesGetCacheableQueriesFromCacheAndPopulatesCacheIfMissing(): void
    {
        $this->cachePool->method('getItem')
            ->with(Stubs\GetUserProfileQuery::CACHE_KEY)
            ->willReturn($this->cacheItem);

        $this->cachePool->expects($this->once())
            ->method('save')
            ->with($this->cacheItem);

        $this->cacheItem->method('isHit')
            ->willReturn(false);

        $this->cacheItem->method('set')
            ->with(DTO\Cache\ResultWrapper::wrap($this->result, 0, $this->cacheableQuery->getTTL()))
            ->willReturn($this->cacheItem);

        $this->cacheItem->method('expiresAfter')
            ->with(Stubs\GetUserProfileQuery::CACHE_TTL)
            ->willReturn($this->cacheItem);

        $this->cacheItem->method('get')
          ->willReturn($this->resultWrapper);

        $result = ($this->queryCacher)($this->cacheableQuery, $this->next);

        $this->assertEquals($this->result, $result);
    }

    public function testForceReloadsItemInCacheIfShouldReloadIsTrueInQuery(): void
    {
        $this->cacheableQuery = new class ('uuid') extends Stubs\GetUserProfileQuery {
            public function shouldRecomputeResult(): bool
            {
                return true;
            }
        };

        $this->cachePool->method('getItem')
          ->with(Stubs\GetUserProfileQuery::CACHE_KEY)
          ->willReturn($this->cacheItem);

        $this->cachePool->expects($this->once())
          ->method('save')
          ->with($this->cacheItem);

        $this->cacheItem->method('isHit')
          ->willReturn(true);

        $this->cacheItem->method('set')
          ->with(DTO\Cache\ResultWrapper::wrap($this->result, 0, $this->cacheableQuery->getTTL()))
          ->willReturn($this->cacheItem);

        $this->cacheItem->method('expiresAfter')
          ->with(Stubs\GetUserProfileQuery::CACHE_TTL)
          ->willReturn($this->cacheItem);

        $this->cacheItem->method('get')
          ->willReturn($this->resultWrapper);

        $result = ($this->queryCacher)($this->cacheableQuery, $this->next);

        $this->assertEquals($this->result, $result);
    }

    public function testForceReloadsItemInCacheIfUnserializedCacheEntryIsCorrupt(): void
    {
        $this->cachePool->method('getItem')
          ->with(Stubs\GetUserProfileQuery::CACHE_KEY)
          ->willReturn($this->cacheItem);

        $this->cachePool->expects($this->once())
          ->method('save')
          ->with($this->cacheItem);

        $this->cacheItem->method('isHit')
          ->willReturn(true);

        $this->cacheItem->method('set')
          ->with(DTO\Cache\ResultWrapper::wrap($this->result, 0, $this->cacheableQuery->getTTL()))
          ->willReturn($this->cacheItem);

        $this->cacheItem->method('expiresAfter')
          ->with(Stubs\GetUserProfileQuery::CACHE_TTL)
          ->willReturn($this->cacheItem);

        $this->cacheItem->method('get')
          ->willReturn(new \__PHP_Incomplete_Class()); // when unserialization fails

        $result = ($this->queryCacher)($this->cacheableQuery, $this->next);

        $this->assertEquals($this->result, $result);
    }

    public function testForceReloadsItemInCacheIfItWillExpireSoon(): void
    {
        $this->resultWrapper = DTO\Cache\ResultWrapper::wrap($this->result, 20, 0);
        $this->cachePool->method('getItem')
          ->with(Stubs\GetUserProfileQuery::CACHE_KEY)
          ->willReturn($this->cacheItem);

        $this->cachePool->expects($this->once())
          ->method('save')
          ->with($this->cacheItem);

        $this->cacheItem->method('isHit')
          ->willReturn(true);

        $this->cacheItem->method('set')
          ->with(DTO\Cache\ResultWrapper::wrap($this->result, 0, $this->cacheableQuery->getTTL()))
          ->willReturn($this->cacheItem);

        $this->cacheItem->method('expiresAfter')
          ->with(Stubs\GetUserProfileQuery::CACHE_TTL)
          ->willReturn($this->cacheItem);

        $this->cacheItem->method('get')
          ->willReturn($this->resultWrapper);

        $result = ($this->queryCacher)($this->cacheableQuery, $this->next);

        $this->assertEquals($this->result, $result);
    }
}
