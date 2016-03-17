<?php
namespace Madewithlove\LaravelPsrCacheBridge\Laravel;

use Exception;
use Illuminate\Contracts\Cache\Repository;
use Madewithlove\LaravelPsrCacheBridge\Exceptions\InvalidArgumentException;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

class CacheItemPool implements CacheItemPoolInterface
{
    /**
     * @var Repository
     */
    private $repository;

    /**
     * @var CacheItemInterface[]
     */
    private $deferred = [];

    /**
     * @param Repository $repository
     */
    public function __construct($repository)
    {
        $this->repository = $repository;
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
        $this->commit();
    }

    /**
     * Returns a Cache Item representing the specified key.
     * This method must always return a CacheItemInterface object, even in case of
     * a cache miss. It MUST NOT return null.
     *
     * @param string $key
     *   The key for which to return the corresponding Cache Item.
     *
     * @throws InvalidArgumentException
     *   If the $key string is not a legal value a \Psr\Cache\InvalidArgumentException
     *   MUST be thrown.
     * @return CacheItemInterface
     *   The corresponding Cache Item.
     */
    public function getItem($key)
    {
        $this->validateKey($key);

        if ($this->repository->has($key)) {
            return new CacheItem($key, unserialize($this->repository->get($key)), true);
        } else {
            return new CacheItem($key);
        }
    }

    /**
     * Returns a traversable set of cache items.
     *
     * @param array $keys
     * An indexed array of keys of items to retrieve.
     *
     * @throws InvalidArgumentException
     *   If any of the keys in $keys are not a legal value a \Psr\Cache\InvalidArgumentException
     *   MUST be thrown.
     * @return array|\Traversable
     *   A traversable collection of Cache Items keyed by the cache keys of
     *   each item. A Cache item will be returned for each key, even if that
     *   key is not found. However, if no keys are specified then an empty
     *   traversable MUST be returned instead.
     */
    public function getItems(array $keys = array())
    {
        foreach ($keys as $key) {
            yield $key => $this->getItem($key);
        }
    }

    /**
     * Confirms if the cache contains specified cache item.
     * Note: This method MAY avoid retrieving the cached value for performance reasons.
     * This could result in a race condition with CacheItemInterface::get(). To avoid
     * such situation use CacheItemInterface::isHit() instead.
     *
     * @param string $key
     *    The key for which to check existence.
     *
     * @throws InvalidArgumentException
     *   If the $key string is not a legal value a \Psr\Cache\InvalidArgumentException
     *   MUST be thrown.
     * @return bool
     *  True if item exists in the cache, false otherwise.
     */
    public function hasItem($key)
    {
        $this->validateKey($key);

        return $this->repository->has($key);
    }

    /**
     * Deletes all items in the pool.
     *
     * @return bool
     *   True if the pool was successfully cleared. False if there was an error.
     */
    public function clear()
    {
        try {
            $this->repository->flush();
        } catch (Exception $exception) {
            return false;
        }

        return true;
    }

    /**
     * Removes the item from the pool.
     *
     * @param string $key
     *   The key for which to delete
     *
     * @throws InvalidArgumentException
     *   If the $key string is not a legal value a \Psr\Cache\InvalidArgumentException
     *   MUST be thrown.
     * @return bool
     *   True if the item was successfully removed. False if there was an error.
     */
    public function deleteItem($key)
    {
        $this->validateKey($key);

        return $this->repository->forget($key);
    }

    /**
     * Removes multiple items from the pool.
     *
     * @param array $keys
     *   An array of keys that should be removed from the pool.
     *
     * @throws InvalidArgumentException
     *   If any of the keys in $keys are not a legal value a \Psr\Cache\InvalidArgumentException
     *   MUST be thrown.
     * @return bool
     *   True if the items were successfully removed. False if there was an error.
     */
    public function deleteItems(array $keys)
    {
        // Validating all keys first.
        foreach ($keys as $key) {
            $this->validateKey($key);
        }

        $success = true;

        foreach ($keys as $key) {
            $success = $success && $this->deleteItem($key);
        }

        return $success;
    }

    /**
     * Persists a cache item immediately.
     *
     * @param CacheItemInterface $item
     *   The cache item to save.
     *
     * @return bool
     *   True if the item was successfully persisted. False if there was an error.
     */
    public function save(CacheItemInterface $item)
    {
        if ($item instanceof CacheItem) {
            $expiresInMinutes = $item->getTTL();
        } else {
            $expiresInMinutes = null;
        }

        if (is_null($expiresInMinutes)) {
            $this->repository->forever($item->getKey(), serialize($item->get()));
        } else {
            $this->repository->put($item->getKey(), serialize($item->get()), $expiresInMinutes);
        }

        return true;
    }

    /**
     * Sets a cache item to be persisted later.
     *
     * @param CacheItemInterface $item
     *   The cache item to save.
     *
     * @return bool
     *   False if the item could not be queued or if a commit was attempted and failed. True otherwise.
     */
    public function saveDeferred(CacheItemInterface $item)
    {
        $this->deferred[] = $item;
    }

    /**
     * Persists any deferred cache items.
     *
     * @return bool
     *   True if all not-yet-saved items were successfully saved or there were none. False otherwise.
     */
    public function commit()
    {
        $success = true;

        foreach ($this->deferred as $item) {
            $success = $success && $this->save($item);
        }

        $this->deferred = [];

        return $success;
    }

    /**
     * @param string $key
     *
     * @throws InvalidArgumentException
     */
    private function validateKey($key)
    {
        if (!is_string($key) || preg_match('#[{}\(\)/\\\\@:]#', $key)) {
            throw new InvalidArgumentException();
        }
    }
}
