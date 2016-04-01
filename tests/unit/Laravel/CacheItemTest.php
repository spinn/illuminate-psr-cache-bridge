<?php
namespace Madewithlove\IlluminatePsrCacheBridge\Tests\Unit\Laravel;

use Carbon\Carbon;
use Madewithlove\IlluminatePsrCacheBridge\Laravel\CacheItem;
use PHPUnit_Framework_TestCase;
use Psr\Cache\CacheItemInterface;

class CacheItemTest extends PHPUnit_Framework_TestCase
{
    /** @test */
    public function it_can_be_constructed()
    {
        // Arrange
        $item = new CacheItem('key', 'value');

        // Act

        // Assert
        $this->assertInstanceOf(CacheItemInterface::class, $item);
        $this->assertInstanceOf(CacheItem::class, $item);
    }

    /** @test */
    public function it_can_say_if_its_a_cache_hit()
    {
        // Arrange
        $itemMiss = new CacheItem('key', 'value');
        $itemHit = new CacheItem('key', 'value', true);

        // Act

        // Assert
        $this->assertTrue($itemHit->isHit());
        $this->assertFalse($itemMiss->isHit());
    }
    
    /** @test */
    public function it_remembers_its_key_and_value()
    {
        // Arrange
        $value = ['foo' => 'bar', 'baz' => ['baz' => new \stdClass()]];
        $item = new CacheItem('key', $value, true);

        // Act

        // Assert
        $this->assertEquals('key', $item->getKey());
        $this->assertEquals($value, $item->get());
    }

    /** @test */
    public function it_can_set_ttl_in_seconds_and_return_minutes()
    {
        // Arrange
        $expiringInExactlyOneMinute = new CacheItem('key1', 'value');
        $expiringInOneMinute = new CacheItem('key2', 'value');
        $expiringNow = new CacheItem('key3', 'value');

        // Act
        $expiringInExactlyOneMinute->expiresAfter(60);
        $expiringInOneMinute->expiresAfter(61);
        $expiringNow->expiresAfter(59);

        // Assert
        $this->assertEquals(1, $expiringInExactlyOneMinute->getTTL());
        $this->assertEquals(1, $expiringInOneMinute->getTTL());
        $this->assertEquals(0, $expiringNow->getTTL());
    }

    /** @test */
    public function it_will_return_null_when_not_expiring()
    {
        // Arrange
        $nonExpiringItem = new CacheItem('key', 'value');

        // Act

        // Assert
        $this->assertNull($nonExpiringItem->getTTL());
    }
    
    /** @test */
    public function it_can_set_expiry_with_datetime()
    {
        // Arrange
        $item = new CacheItem('key', 'value');

        // Act
        Carbon::setTestNow($now = Carbon::now());
        $item->expiresAt($now->addMinute());

        // Assert
        $this->assertEquals(1, $item->getTTL());
    }

    /** @test */
    public function it_will_set_ttl_to_zero_if_datetime_in_the_past()
    {
        // Arrange
        $item = new CacheItem('key', 'value');

        // Act
        Carbon::setTestNow($now = Carbon::now());
        $item->expiresAt($now->subMinute());

        // Assert
        $this->assertEquals(0, $item->getTTL());
    }

    /** @test */
    public function it_will_set_ttl_correctly_when_given_datetime_interval()
    {
        // Arrange
        $item = new CacheItem('key', 'value');

        // Act
        Carbon::setTestNow($now = Carbon::now());
        $plusTwoMinutes = $now->copy()->addMinutes(2);
        $item->expiresAt($now->diff($plusTwoMinutes));

        // Assert
        $this->assertEquals(2, $item->getTTL());
    }

    /** @test */
    public function it_will_set_ttl_to_zero_when_datetime_interval_is_inverted()
    {
        // Arrange
        $item = new CacheItem('key', 'value');

        // Act
        Carbon::setTestNow($now = Carbon::now());
        $plusTwoMinutes = $now->copy()->addMinutes(2);
        $item->expiresAt($plusTwoMinutes->diff($now));

        // Assert
        $this->assertEquals(0, $item->getTTL());
    }

    /** @test */
    public function it_will_remember_value_that_has_been_set()
    {
        // Arrange
        $item = new CacheItem('foo');

        // Act
        $item->set('bar');

        // Assert
        $this->assertEquals('bar', $item->get());
    }
}
