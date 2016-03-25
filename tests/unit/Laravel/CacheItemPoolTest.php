<?php
namespace Madewithlove\IlluminatePsrCacheBridge\Tests\Unit\Laravel;

use Illuminate\Contracts\Cache\Repository;
use Madewithlove\IlluminatePsrCacheBridge\Laravel\CacheItemPool;
use PHPUnit_Framework_TestCase;
use Psr\Cache\CacheItemPoolInterface;

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

    public function it_can_say_if_is_cache_hit()
    {
        // Arrange
        
        
        // Act
        // Assert
    }
}
