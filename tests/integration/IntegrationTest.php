<?php
namespace Madewithlove\IlluminatePsrCacheBridge\Tests\Integration;

use Cache\IntegrationTests\CachePoolTest;
use Illuminate\Cache\MemcachedStore;
use Illuminate\Cache\Repository;
use Madewithlove\IlluminatePsrCacheBridge\Laravel\CacheItemPool;
use Memcached;
use Psr\Cache\CacheItemPoolInterface;

class IntegrationTest extends CachePoolTest
{
    protected $skippedTests = [
        'testBasicUsageWithLongKey' => 'Memcached does not support key lenght over 250 characters.',
    ];

    /**
     * @return CacheItemPoolInterface that is used in the tests
     */
    public function createCachePool()
    {
        $memcache = new Memcached();
        $memcache->addServer('localhost', 11211);

        return new CacheItemPool(new Repository(new MemcachedStore($memcache)));
    }
}
