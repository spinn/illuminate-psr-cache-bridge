<?php
namespace Madewithlove\IlluminatePsrCacheBridge\Tests\Integration;

use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository;
use Madewithlove\IlluminatePsrCacheBridge\Laravel\CacheItemPool;
use PHPUnit_Framework_TestCase;

class IntegrationTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \Illuminate\Contracts\Cache\Repository
     */
    private $repository;

    /**
     * @var \Psr\Cache\CacheItemPoolInterface
     */
    private $pool;

    /**
     * Set up before test.
     */
    public function setUp()
    {
        $this->repository = new Repository(new ArrayStore());
        $this->pool = new CacheItemPool($this->repository);
    }

    /** @test */
    public function empty_store_gives_cache_miss()
    {
        // Arrange

        // Act
        $item = $this->pool->getItem('foo');

        // Assert
        $this->assertFalse($item->isHit());
    }
}
