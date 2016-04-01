<?php
namespace Madewithlove\IlluminatePsrCacheBridge\Tests\Unit\Laravel;

use Illuminate\Contracts\Cache\Repository;
use Madewithlove\IlluminatePsrCacheBridge\Laravel\CacheItemPool;
use PHPUnit_Framework_TestCase;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;

class CacheItemPoolTest extends PHPUnit_Framework_TestCase
{
    /** @test */
    public function it_can_be_constructed()
    {
        // Arrange
        $repository = $this->getMockBuilder(Repository::class)->getMock();

        // Act
        $pool = new CacheItemPool($repository);

        // Assert
        $this->assertInstanceOf(CacheItemPoolInterface::class, $pool);
        $this->assertInstanceOf(CacheItemPool::class, $pool);
    }

    /** @test */
    public function it_can_retrieve_items()
    {
        // Arrange
        $repository = $this->getMockBuilder(Repository::class)->getMock();
        $repository->method('has')->with('key')->willReturn(false);

        $pool = new CacheItemPool($repository);

        // Act
        $item = $pool->getItem('key');

        // Assert
        $this->assertFalse($item->isHit());
    }

    /** @test */
    public function it_can_get_a_single_item()
    {
        // Arrange
        $repository = $this->getMockBuilder(Repository::class)->getMock();
        $repository->method('has')->with('foo')->willReturn(true);
        $repository->method('get')->with('foo')->willReturn(serialize('bar'));

        $pool = new CacheItemPool($repository);

        // Act
        $item = $pool->getItem('foo');

        // Assert
        $this->assertEquals('foo', $item->getKey());
        $this->assertEquals('bar', $item->get());
        $this->assertTrue($item->isHit());
    }

    /** @test */
    public function it_can_get_multiple_items()
    {
        // Arrange
        $repository = $this->getMockBuilder(Repository::class)->getMock();
        $repository
            ->method('has')
            ->withConsecutive(['foo'], ['baz'], ['qux'])
            ->willReturnOnConsecutiveCalls(true, true, false);
        $repository
            ->method('get')
            ->withConsecutive(['foo'], ['baz'])
            ->willReturnOnConsecutiveCalls(serialize('bar'), serialize('qux'));

        $pool = new CacheItemPool($repository);

        // Act
        $items = $pool->getItems(['foo', 'baz', 'qux']);

        // Assert
        $this->assertEquals('foo', $items[0]->getKey());
        $this->assertEquals('bar', $items[0]->get());
        $this->assertTrue($items[0]->isHit());

        $this->assertEquals('baz', $items[1]->getKey());
        $this->assertEquals('qux', $items[1]->get());
        $this->assertTrue($items[1]->isHit());

        $this->assertEquals('qux', $items[2]->getKey());
        $this->assertNull($items[2]->get());
        $this->assertFalse($items[2]->isHit());
    }

    /** @test */
    public function it_throws_exception_for_invalid_key()
    {
        // Arrange
        $this->setExpectedException(InvalidArgumentException::class);

        $repository = $this->getMockBuilder(Repository::class)->getMock();

        $pool = new CacheItemPool($repository);

        // Act
        $pool->hasItem('foo@bar');

        // Assert
    }

    /** @test */
    public function it_returns_if_repository_has_item()
    {
        // Arrange
        $repository = $this->getMockBuilder(Repository::class)->getMock();
        $repository->method('has')->with('foo')->willReturn(false);
        $pool = new CacheItemPool($repository);

        // Act
        $bool = $pool->hasItem('foo');

        // Assert
        $this->assertFalse($bool);
    }
}
