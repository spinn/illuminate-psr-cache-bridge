<?php
namespace Madewithlove\IlluminatePsrCacheBridge\Tests\Unit\Laravel;

use Exception;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Contracts\Cache\Store;
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
        $this->assertArrayHasKey('foo', $items);
        $this->assertArrayHasKey('baz', $items);
        $this->assertArrayHasKey('qux', $items);

        $this->assertEquals('foo', $items['foo']->getKey());
        $this->assertEquals('bar', $items['foo']->get());
        $this->assertTrue($items['foo']->isHit());

        $this->assertEquals('baz', $items['baz']->getKey());
        $this->assertEquals('qux', $items['baz']->get());
        $this->assertTrue($items['baz']->isHit());

        $this->assertEquals('qux', $items['qux']->getKey());
        $this->assertNull($items['qux']->get());
        $this->assertFalse($items['qux']->isHit());
    }

    /** @test */
    public function it_throws_exception_for_invalid_key()
    {
        // Arrange
        $repository = $this->getMockBuilder(Repository::class)->getMock();
        $pool = new CacheItemPool($repository);

        $this->setExpectedException(InvalidArgumentException::class);

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

    /** @test */
    public function it_returns_empty_array_if_empty_array_is_passed_to_get_items_method()
    {
        // Arrange
        $repository = $this->getMockBuilder(Repository::class)->getMock();
        $pool = new CacheItemPool($repository);

        // Act
        $result = $pool->getItems([]);

        // Assert
        $this->assertEmpty($result);
    }

    /** @test */
    public function it_returns_false_when_item_deletion_fails()
    {
        // Arrange
        $repository = $this->getMockBuilder(Repository::class)->getMock();
        $repository->method('has')->with('bar')->willReturn(true);
        $repository->method('forget')->with('bar')->willReturn(false);
        $pool = new CacheItemPool($repository);

        // Act
        $result = $pool->deleteItem('bar');

        // Assert
        $this->assertFalse($result);
    }

    /** @test */
    public function it_returns_true_when_repository_doesnt_have_it()
    {
        // Arrange
        $repository = $this->getMockBuilder(Repository::class)->getMock();
        $repository->method('has')->with('bar')->willReturn(false);
        $pool = new CacheItemPool($repository);

        // Act
        $result = $pool->deleteItem('bar');

        // Assert
        $this->assertTrue($result);
    }

    /** @test */
    public function it_validates_key_before_deletion()
    {
        // Arrange
        $repository = $this->getMockBuilder(Repository::class)->getMock();
        $pool = new CacheItemPool($repository);

        $this->setExpectedException(InvalidArgumentException::class);

        // Act
        $pool->deleteItem('@');

        // Assert
    }

    /** @test */
    public function it_validates_all_keys_before_deleting_items()
    {
        // Arrange
        $repository = $this->getMockBuilder(Repository::class)->getMock();
        $pool = new CacheItemPool($repository);

        $this->setExpectedException(InvalidArgumentException::class);

        // Act
        $pool->deleteItems(['bar', 'foo', '{', '@']);

        // Assert
    }

    /** @test */
    public function it_calls_repository_forget_for_each_item()
    {
        // Arrange
        $repository = $this->getMockBuilder(Repository::class)->getMock();
        $repository->method('forget')
            ->withConsecutive(['bar'], ['foo'], ['baz'])
            ->willReturnOnConsecutiveCalls(true, true, true);
        $pool = new CacheItemPool($repository);

        // Act
        $result = $pool->deleteItems(['bar', 'foo', 'baz']);

        // Assert
        $this->assertTrue($result);
    }

    /** @test */
    public function it_calls_repository_forget_for_each_item_and_returns_false_when_one_fails()
    {
        // Arrange
        $repository = $this->getMockBuilder(Repository::class)->getMock();
        $repository->method('has')
            ->withConsecutive(['bar'], ['foo'], ['baz'])
            ->willReturnOnConsecutiveCalls(true, true, true);
        $repository->method('forget')
            ->withConsecutive(['bar'], ['foo'], ['baz'])
            ->willReturnOnConsecutiveCalls(true, false, true);
        $pool = new CacheItemPool($repository);

        // Act
        $result = $pool->deleteItems(['bar', 'foo', 'baz']);

        // Assert
        $this->assertFalse($result);
    }

    /** @test */
    public function it_returns_true_if_empty_array_is_passed_delete_items_method()
    {
        // Arrange
        $repository = $this->getMockBuilder(Repository::class)->getMock();
        $pool = new CacheItemPool($repository);

        // Act
        $result = $pool->deleteItems([]);

        // Assert
        $this->assertTrue($result);
    }

    /** @test */
    public function it_returns_false_when_implementation_fails_during_clear_op()
    {
        // Arrange
        $store = $this->getMockBuilder(Store::class)->getMock();
        $store->method('flush')->willThrowException(new Exception());
        $repository = new \Illuminate\Cache\Repository($store);
        $pool = new CacheItemPool($repository);

        // Act
        $result = $pool->clear();

        // Assert
        $this->assertFalse($result);
    }

    /** @test */
    public function it_returns_true_when_clear_op_succeeds()
    {
        // Arrange
        $store = $this->getMockBuilder(Store::class)->getMock();
        $store->method('flush')->willReturn(null);
        $repository = new \Illuminate\Cache\Repository($store);
        $pool = new CacheItemPool($repository);

        // Act
        $result = $pool->clear();

        // Assert
        $this->assertTrue($result);
    }

    /** @test */
    public function it_can_defer_saving()
    {
        // Arrange
        $repository = $this->getMockBuilder(Repository::class)->getMock();
        $pool = new CacheItemPool($repository);

        // Act
        $result = $pool->saveDeferred($pool->getItem('bar')->set('baz'));

        // Assert
        $this->assertTrue($result);

        // Arrange
        $repository->method('forever')->with('bar', serialize('baz'))->willReturn(true);

        // Act
        $result = $pool->commit();

        // Assert
        $this->assertTrue($result);
    }

    /** @test */
    public function it_saves_deferred_items_when_destructed()
    {
        // Arrange
        $repository = $this->getMockBuilder(Repository::class)->getMock();
        $pool = new CacheItemPool($repository);

        // Act
        $result = $pool->saveDeferred($pool->getItem('bar')->set('baz'));

        // Assert
        $this->assertTrue($result);

        // Arrange
        $repository->method('forever')->with('bar', serialize('baz'))->willReturn(true);

        // Act
        $pool = null;
        gc_collect_cycles();
    }

    /** @test */
    public function it_saves_items_with_a_rounded_down_to_minutes_ttl()
    {
        // Arrange
        $seconds = 65;
        $minutes = 1;
        $repository = $this->getMockBuilder(Repository::class)->getMock();
        $repository->method('put')->with('bar', serialize('baz'), $minutes)->willReturn(true);
        $pool = new CacheItemPool($repository);

        // Act
        $result = $pool->save($pool->getItem('bar')->set('baz')->expiresAfter($seconds));

        // Assert
        $this->assertTrue($result);
    }

    /** @test */
    public function it_returns_false_when_save_fails()
    {
        // Arrange
        $repository = $this->getMockBuilder(Repository::class)->getMock();
        $repository->method('forever')->with('bar', serialize('baz'))->willThrowException(new Exception());
        $pool = new CacheItemPool($repository);

        // Act
        $result = $pool->save($pool->getItem('bar')->set('baz'));

        // Assert
        $this->assertFalse($result);
    }

    /** @test */
    public function it_has_item_when_a_deferred_item_is_not_expired()
    {
        // Arrange
        $repository = $this->getMockBuilder(Repository::class)->getMock();
        $pool = new CacheItemPool($repository);
        $key = 'bar';

        // Act
        $item = $pool->getItem($key);
        $pool->saveDeferred($item->set('baz')->expiresAt(new \DateTimeImmutable('+ 1 seconds')));

        // Assert
        $this->assertTrue($pool->hasItem($key));
    }

    public function it_returns_false_when_repository_errors_on_put()
    {
        // Arrange
        $repository = $this->getMockBuilder(Repository::class)->getMock();
        $repository->method('put')->with('key', serialize('value'), 2)->willThrowException(new Exception());
        $pool = new CacheItemPool($repository);

        // Act
        $result = $pool->save($pool->getItem('key')->set('value')->expiresAfter(120));

        // Assert
        $this->assertFalse($result);
    }
}
