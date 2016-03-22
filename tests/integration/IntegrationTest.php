<?php
namespace Madewithlove\IlluminatePsrCacheBridge\Tests\Integration;

use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\MemcachedStore;
use Illuminate\Cache\Repository;
use Madewithlove\IlluminatePsrCacheBridge\Laravel\CacheItem;
use Madewithlove\IlluminatePsrCacheBridge\Laravel\CacheItemPool;
use Memcached;
use PHPUnit_Framework_TestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

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
        $this->assertEquals('foo', $item->getKey());
        $this->assertNull($item->get());
    }

    /** @test */
    public function it_gets_store_serialized()
    {
        // Arrange
        $foo = $this->pool->getItem('foo');
        $foo->set('bar');
        $this->pool->save($foo);

        // Act

        // Assert
        $this->assertEquals(serialize('bar'), $this->repository->get('foo'));
    }

    /** @test */
    public function it_gets_returned_unserialized()
    {
        // Arrange
        $this->repository->put('foo', serialize('bar'), 1);

        // Act
        $foo = $this->pool->getItem('foo');

        // Assert
        $this->assertTrue($foo->isHit());
        $this->assertEquals('bar', $foo->get());
    }

    /** @test */
    public function it_produces_a_cache_miss()
    {
        // Arrange
        $memcache = new Memcached();
        $memcache->flush();
        $this->repository = new Repository(new MemcachedStore($memcache));
        $this->pool = new CacheItemPool($this->repository);

        // Act
        $foo = $this->pool->getItem('foo');
        $this->pool->save($foo->set('bar')->expiresAfter(1));
        sleep(2); // The item MUST be expired after 2 seconds.

        // Assert
        $foo = $this->pool->getItem('foo');
        $this->assertFalse($foo->isHit());
        $this->assertFalse($this->pool->hasItem('foo'));
    }

    /** @test */
    public function it_can_retrieve_multiple_items()
    {
        // Arrange
        $this->pool->save(new CacheItem('foo', 'bar'));

        // Act
        $items = $this->pool->getItems(['foo', 'baz']);

        // Assert
        /* @var CacheItemInterface[] $items */
        foreach ($items as $item) {
            if ($item->getKey() === 'foo') {
                $this->assertEquals('bar', $item->get());
                $this->assertTrue($item->isHit());
            }
            if ($item->getKey() === 'baz') {
                $this->assertFalse($item->isHit());
            }
        }

        $keys = array_map(function (CacheItemInterface $item) {
            return $item->getKey();
        }, $items);

        sort($keys);

        $this->assertEquals(['baz', 'foo'], $keys);
    }

    /** @test */
    public function it_can_clear_the_pool()
    {
        // Arrange
        $this->pool->save(new CacheItem('foo', 'foo'));
        $this->pool->save(new CacheItem('bar', 'bar'));
        $this->pool->save(new CacheItem('baz', 'baz'));

        // Act
        $this->pool->clear();
        $items = $this->pool->getItems(['foo', 'bar', 'baz']);
        $hits = array_map(function (CacheItemInterface $item) {
            return $item->isHit();
        }, $items);

        // Assert
        $this->assertEquals([false, false, false], $hits);
    }

    /** @test */
    public function it_returns_true_when_saved()
    {
        // Arrange
        $item = new CacheItem('foo', 'foo');

        // Act
        $result = $this->pool->save($item);

        // Assert
        $this->assertTrue($result);
        $this->assertTrue($this->pool->getItem('foo')->isHit());
    }

    /** @test */
    public function it_can_defer_saving()
    {
        // Arrange
        $item = new CacheItem('foo', 'foo');

        // Act
        $this->pool->saveDeferred($item);

        // Assert
        $this->assertFalse($this->pool->getItem('foo')->isHit());

        // Act
        $this->pool->commit();

        // Assert
        $this->assertTrue($this->pool->getItem('foo')->isHit());
        $this->assertEquals('foo', $this->pool->getItem('foo')->get());
    }

    /** @test */
    public function it_can_store_complex_arrays()
    {
        // Arrange
        $complex = ['foo' => ['bar' => 'bar', 'bool' => false], 'baz' => new \stdClass()];

        // Act
        $this->pool->save(new CacheItem('key', $complex));

        // Assert
        $this->assertEquals($complex, $this->pool->getItem('key')->get());
    }

    /** @test */
    public function it_can_delete_stored_items()
    {
        // Arrange
        $this->pool->save(new CacheItem('foo', 'foo'));

        // Act
        $this->pool->deleteItem('foo');

        // Assert
        $this->assertFalse($this->pool->getItem('foo')->isHit());
    }

    /** @test */
    public function it_can_delete_multiple_items()
    {
        // Arrange
        $this->pool->save(new CacheItem('foo', 'foo'));
        $this->pool->save(new CacheItem('bar', 'bar'));

        // Act
        $this->pool->deleteItems(['foo', 'bar', 'baz']);

        // Assert
        $this->assertFalse($this->pool->getItem('foo')->isHit());
        $this->assertFalse($this->pool->getItem('bar')->isHit());
        $this->assertFalse($this->pool->getItem('baz')->isHit());
    }

    /** @test */
    public function it_returns_whether_it_removed_item()
    {
        // Arrange

        // Act
        $result1 = $this->pool->deleteItem('foo');
        $result2 = $this->pool->deleteItems(['bar', 'baz']);

        // Assert
        $this->assertTrue($result1);
        $this->assertTrue($result2);
    }
}
