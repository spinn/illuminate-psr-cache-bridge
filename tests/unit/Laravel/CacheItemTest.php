<?php
namespace Madewithlove\IlluminatePsrCacheBridge\Tests\Unit\Laravel;

use DateTime;
use DateTimeImmutable;
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
        $this->assertSame('key', $item->getKey());
        $this->assertSame($value, $item->get());
    }

    /** @test */
    public function it_can_set_ttl_in_seconds()
    {
        // Arrange
        $expiringInExactlyOneMinute = (new CacheItem('key1', 'value'))->expiresAfter(60);

        // Act

        // Assert
        $this->assertSame(
            (new DateTimeImmutable('+ 1 minute'))->format('d-m-Y H:i:s'),
            $expiringInExactlyOneMinute->getExpiresAt()->format('d-m-Y H:i:s')
        );
    }

    /** @test */
    public function it_can_set_ttl_with_date_interval()
    {
        // Arrange
        $expiringInExactlyOneMinute = (new CacheItem('key1', 'value'))->expiresAfter(new \DateInterval('PT1M'));

        // Act

        // Assert
        $this->assertSame(
            (new DateTimeImmutable('+ 1 minute'))->format('d-m-Y H:i:s'),
            $expiringInExactlyOneMinute->getExpiresAt()->format('d-m-Y H:i:s')
        );
    }

    /** @test */
    public function it_can_set_expiry_with_datetime()
    {
        // Arrange
        $item = new CacheItem('key', 'value');

        // Act
        $now = new DateTimeImmutable();
        $inOneMinute = clone $now;
        $inOneMinute->modify('+1 minute');
        $item->expiresAt($inOneMinute);

        // Assert
        $this->assertSame(
            $now->format('d-m-Y H:i:s'),
            $item->getExpiresAt()->format('d-m-Y H:i:s')
        );
    }

    /** @test */
    public function it_will_return_null_when_not_expiring()
    {
        // Arrange
        $nonExpiringItem = new CacheItem('key', 'value');

        // Act

        // Assert
        $this->assertNull($nonExpiringItem->getExpiresAt());
    }

    /** @test */
    public function it_will_remember_value_that_has_been_set()
    {
        // Arrange
        $item = new CacheItem('foo');

        // Act
        $item->set('bar');

        // Assert
        $this->assertSame('bar', $item->get());
    }

    /** @test */
    public function it_will_not_keep_reference_to_passed_date_time_interfaced_object()
    {
        // Arrange
        $item = new CacheItem('foo');

        // Act
        $now = new DateTime();
        $item->expiresAt($now);
        $now->modify('+1 minute');

        // Assert
        $this->assertNotEquals($now, $item->getExpiresAt());
    }
}
