<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Cache;

use DateInterval;
use Phalcon\Cache\Adapter\AdapterInterface;
use Phalcon\Cache\Adapter\Redis;
use Phalcon\Cache\Exception\Exception;
use Phalcon\Events\EventsAwareInterface;
use Phalcon\Events\Exception as EventsException;
use Phalcon\Events\Traits\EventsAwareTrait;
use Psr\SimpleCache\CacheInterface;

/**
 * This component offers caching capabilities for your application.
 * Phalcon\Cache implements PSR-16.
 */
abstract class AbstractCache implements CacheInterface, EventsAwareInterface
{
    use EventsAwareTrait;

    /**
     * Constructor.
     *
     * @param AdapterInterface $adapter The cache adapter
     */
    public function __construct(
        protected AdapterInterface $adapter
    ) {
    }

    /**
     * Returns the current adapter
     *
     * @return AdapterInterface
     */
    public function getAdapter(): AdapterInterface
    {
        return $this->adapter;
    }

    /**
     * Checks the key. If it contains invalid characters an exception is thrown
     *
     * @param string $key
     *
     * @throws Exception
     */
    protected function checkKey(string $key): void
    {
        if (preg_match("/[^A-Za-z0-9-_.]/", $key)) {
            throw new Exception(
                "The key contains invalid characters"
            );
        }
    }

    /**
     * Wipes clean the entire cache's keys.
     *
     * @return bool True on success and false on failure.
     */
    protected function doClear(): bool
    {
        return $this->adapter->clear();
    }

    /**
     * Delete an item from the cache by its unique key.
     *
     * @param string $key The unique cache key of the item to delete.
     *
     * @return bool True if the item was successfully removed. False if there
     *              was an error.
     *
     * @throws Exception MUST be thrown if the $key string is
     *                                  not a legal value.
     * @throws EventsException
     */
    protected function doDelete(string $key): bool
    {
        $this->fireManagerEvent("cache:beforeDelete", $key);

        $this->checkKey($key);

        $result = $this->adapter->delete($key);

        $this->fireManagerEvent("cache:afterDelete", $key);

        return $result;
    }

    /**
     * Deletes multiple cache items in a single operation.
     *
     * @param iterable<mixed, mixed> $keys A list of string-based keys to be deleted.
     *
     * @return bool True if the items were successfully removed. False if there
     *              was an error.
     *
     * @throws Exception MUST be thrown if $keys is neither an
     *                                  array nor a Traversable, or if any of
     *                                  the $keys are not a legal value.
     * @throws EventsException
     */
    protected function doDeleteMultiple(iterable $keys): bool
    {
        $this->fireManagerEvent("cache:beforeDeleteMultiple", $keys);

        $result = true;
        /** @var string $key */
        foreach ($keys as $key) {
            if (true !== $this->adapter->delete($key)) {
                $result = false;
            }
        }

        $this->fireManagerEvent("cache:afterDeleteMultiple", $keys);

        return $result;
    }

    /**
     * Fetches a value from the cache.
     *
     * @param string $key     The unique key of this item in the cache.
     * @param mixed  $default Default value to return if the key does not exist.
     *
     * @return mixed The value of the item from the cache, or $default in case
     * of cache miss.
     *
     * @throws Exception MUST be thrown if the $key string is
     * not a legal value.
     * @throws EventsException
     */
    protected function doGet(string $key, mixed $default = null)
    {
        $this->fireManagerEvent('cache:beforeGet', $key);

        $this->checkKey($key);

        $result = $this->adapter->get($key, $default);

        $this->fireManagerEvent('cache:afterGet', $key);

        return $result;
    }

    /**
     * Obtains multiple cache items by their unique keys.
     *
     * @param iterable<mixed, mixed> $keys    A list of keys that can obtained
     *                                        in a single operation.
     * @param mixed                  $default Default value to return for keys
     *                                        that do not exist.
     *
     * @return iterable<array-key, mixed> A list of key => value pairs. Cache
     * keys that do not exist or are stale will have $default as value.
     *
     * @throws Exception MUST be thrown if $keys is neither an
     * array nor a Traversable, or if any of the $keys are not a legal value.
     * @throws EventsException
     */
    protected function doGetMultiple(
        iterable $keys,
        mixed $default = null
    ): iterable {
        $this->fireManagerEvent('cache:beforeGetMultiple', $keys);

        $adapterClass = get_class($this->adapter);
        if ($adapterClass === Redis::class) {
            $results    = $this->adapter->getAdapter()->mget($keys);
            $serializer = $this->adapter->getSerializer();
            $results    = array_map(
                function ($element) use ($serializer, $default) {
                    $serializer->unserialize($element);
                    return false === $element
                        ? $default
                        : $serializer->getData();
                },
                $results
            );
            $results    = array_combine($keys, $results);
        } else {
            $results = [];
            /** @var string $element */
            foreach ($keys as $element) {
                $results[$element] = $this->get($element, $default);
            }
        }

        $this->fireManagerEvent('cache:afterGetMultiple', $keys);

        return $results;
    }

    /**
     * Determines whether an item is present in the cache.
     *
     * @param string $key The cache item key.
     *
     * @return bool
     *
     * @throws Exception MUST be thrown if the $key string is
     * not a legal value.
     * @throws EventsException
     */
    protected function doHas(string $key): bool
    {
        $this->fireManagerEvent('cache:beforeHas', $key);

        $this->checkKey($key);

        $result = $this->adapter->has($key);

        $this->fireManagerEvent('cache:afterHas', $key);

        return $result;
    }

    /**
     * Persists data in the cache, uniquely referenced by a key with an optional
     * expiration TTL time.
     *
     * @param string                $key    The key of the item to store.
     * @param mixed                 $value  The value of the item to store.
     *                                      Must be serializable.
     * @param null|int|DateInterval $ttl    Optional. The TTL value of this
     *                                      item. If no value is sent and the
     *                                      driver supports TTL then the library
     *                                      may set a default value for it or
     *                                      let the driver take care of that.
     *
     * @return bool True on success and false on failure.
     *
     * @throws Exception MUST be thrown if the $key string is not
     * a legal value.
     * @throws EventsException
     */
    protected function doSet(
        string $key,
        mixed $value,
        null | int | DateInterval $ttl = null
    ): bool {
        $this->fireManagerEvent('cache:beforeSet', $key);

        $this->checkKey($key);

        $result = $this->adapter->set($key, $value, $ttl);

        $this->fireManagerEvent('cache:afterSet', $key);

        return $result;
    }

    /**
     * Persists a set of key => value pairs in the cache, with an optional TTL.
     *
     * @param iterable<mixed, mixed> $values A list of key => value pairs for a
     *                                       multiple-set operation.
     * @param null|int|DateInterval  $ttl    Optional. The TTL value of this
     *                                       item. If no value is sent and the
     *                                       driver supports TTL then the
     *                                       library may set a default value for
     *                                       it or let the driver take care of
     *                                       that.
     *
     * @return bool True on success and false on failure.
     *
     * @throws Exception MUST be thrown if $values is neither an
     * array nor a Traversable, or if any of the $values are not a legal value.
     * @throws EventsException
     */
    protected function doSetMultiple(
        iterable $values,
        null | int | DateInterval $ttl = null
    ): bool {
        $this->fireManagerEvent('cache:beforeSetMultiple', $values);

        $result = true;
        /**
         * @var string $key
         * @var mixed  $value
         */
        foreach ($values as $key => $value) {
            if (true !== $this->set($key, $value, $ttl)) {
                $result = false;
            }
        }

        $this->fireManagerEvent('cache:afterSetMultiple', $values);

        return $result;
    }
}
