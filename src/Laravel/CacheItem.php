<?php
namespace Madewithlove\LaravelPsrCacheBridge\Laravel;

use DateInterval;
use DateTime;
use Psr\Cache\CacheItemInterface;

class CacheItem implements CacheItemInterface
{
    /**
     * @var string
     */
    private $key;

    /**
     * @var mixed|null
     */
    private $value;

    /**
     * @var bool
     */
    private $hit;

    /**
     * @var DateTime|DateInterval|int
     */
    private $expires;

    /**
     * CacheItem constructor.
     *
     * @param string $key
     * @param mixed $value
     * @param bool $hit
     */
    public function __construct($key, $value = null, $hit = false)
    {
        $this->key = $key;
        $this->value = $value;
        $this->hit = boolval($hit);
    }

    /**
     * Returns the key for the current cache item.
     * The key is loaded by the Implementing Library, but should be available to
     * the higher level callers when needed.
     *
     * @return string
     *   The key string for this cache item.
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Retrieves the value of the item from the cache associated with this object's key.
     * The value returned must be identical to the value originally stored by set().
     * If isHit() returns false, this method MUST return null. Note that null
     * is a legitimate cached value, so the isHit() method SHOULD be used to
     * differentiate between "null value was found" and "no value was found."
     *
     * @return mixed
     *   The value corresponding to this cache item's key, or null if not found.
     */
    public function get()
    {
        return $this->value;
    }

    /**
     * Confirms if the cache item lookup resulted in a cache hit.
     * Note: This method MUST NOT have a race condition between calling isHit()
     * and calling get().
     *
     * @return bool
     *   True if the request resulted in a cache hit. False otherwise.
     */
    public function isHit()
    {
        return $this->hit;
    }

    /**
     * Sets the value represented by this cache item.
     * The $value argument may be any item that can be serialized by PHP,
     * although the method of serialization is left up to the Implementing
     * Library.
     *
     * @param mixed $value
     *   The serializable value to be stored.
     *
     * @return static
     *   The invoked object.
     */
    public function set($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Sets the expiration time for this cache item.
     *
     * @param \DateTimeInterface $expiration
     *   The point in time after which the item MUST be considered expired.
     *   If null is passed explicitly, a default value MAY be used. If none is set,
     *   the value should be stored permanently or for as long as the
     *   implementation allows.
     *
     * @return static
     *   The called object.
     */
    public function expiresAt($expiration)
    {
        $this->expires = $expiration;

        return $this;
    }

    /**
     * Sets the expiration time for this cache item.
     *
     * @param int|DateInterval $time
     *   The period of time from the present after which the item MUST be considered
     *   expired. An integer parameter is understood to be the time in seconds until
     *   expiration. If null is passed explicitly, a default value MAY be used.
     *   If none is set, the value should be stored permanently or for as long as the
     *   implementation allows.
     *
     * @return static
     *   The called object.
     */
    public function expiresAfter($time)
    {
        $this->expires = $time;

        return $this;
    }

    /**
     * @return int|null
     *   The amount of minutes this item should stay alive. Or null when no expires is given.
     */
    public function getTTL()
    {
        if (is_int($this->expires)) {
            return floor($this->expires / 60.0);
        }

        if ($this->expires instanceof DateTime) {
            $diff = (new DateTime())->diff($this->expires);

            return boolval($diff->invert) ? 0 : $diff->i;
        }

        if ($this->expires instanceof DateInterval) {
            return boolval($this->expires->invert) ? 0 : $this->expires->i;
        }
    }
}
