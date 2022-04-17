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
use Phalcon\Cache\Exception\InvalidArgumentException;
use Traversable;

/**
 * This component offers caching capabilities for your application.
 * Phalcon\Cache implements PSR-16.
 *
 * @property AdapterInterface $adapter
 */
abstract class AbstractCache implements CacheInterface
{
    /**
     * The adapter
     *
     * @var AdapterInterface
     */
    protected AdapterInterface $adapter;

    /**
     * Constructor.
     *
     * @param AdapterInterface $adapter The cache adapter
     */
    public function __construct(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
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
     * @param mixed $key
     *
     * @throws InvalidArgumentException
     */
    protected function checkKey(string $key): void
    {
        if (preg_match("/[^A-Za-z0-9-_.]/", $key)) {
            $exception = $this->getExceptionClass();
            throw new $exception(
                "The key contains invalid characters"
            );
        }
    }

    /**
     * Checks the key. If it contains invalid characters an exception is thrown
     *
     * @param mixed $keys
     *
     * @throws InvalidArgumentException
     */
    protected function checkKeys($keys): void
    {
        if (!(true === is_array($keys) || $keys instanceof Traversable)) {
            $exception = $this->getExceptionClass();
            throw new $exception(
                "The keys need to be an array or instance of Traversable"
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
     * @throws InvalidArgumentException MUST be thrown if the $key string is
     *                                  not a legal value.
     */
    protected function doDelete(string $key): bool
    {
        $this->checkKey($key);

        return $this->adapter->delete($key);
    }

    /**
     * Deletes multiple cache items in a single operation.
     *
     * @param iterable<mixed, mixed> $keys A list of string-based keys to be deleted.
     *
     * @return bool True if the items were successfully removed. False if there
     *              was an error.
     *
     * @throws InvalidArgumentException MUST be thrown if $keys is neither an
     *                                  array nor a Traversable, or if any of
     *                                  the $keys are not a legal value.
     */
    protected function doDeleteMultiple($keys): bool
    {
        $this->checkKeys($keys);

        $result = true;
        foreach ($keys as $key) {
            if (true !== $this->adapter->delete($key)) {
                $result = false;
            }
        }

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
     * @throws InvalidArgumentException MUST be thrown if the $key string is
     * not a legal value.
     */
    protected function doGet(string $key, $default = null)
    {
        $this->checkKey($key);

        return $this->adapter->get($key, $default);
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
     * @throws InvalidArgumentException MUST be thrown if $keys is neither an
     * array nor a Traversable, or if any of the $keys are not a legal value.
     */
    protected function doGetMultiple($keys, $default = null)
    {
        $this->checkKeys($keys);

        $results = [];
        foreach ($keys as $element) {
            $results[$element] = $this->get($element, $default);
        }

        return $results;
    }

    /**
     * Determines whether an item is present in the cache.
     *
     * @param string $key The cache item key.
     *
     * @return bool
     *
     * @throws InvalidArgumentException MUST be thrown if the $key string is
     * not a legal value.
     */
    protected function doHas(string $key): bool
    {
        $this->checkKey($key);

        return $this->adapter->has($key);
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
     * @throws InvalidArgumentException MUST be thrown if the $key string is not
     * a legal value.
     */
    protected function doSet(string $key, $value, $ttl = null): bool
    {
        $this->checkKey($key);

        return $this->adapter->set($key, $value, $ttl);
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
     * @throws InvalidArgumentException MUST be thrown if $values is neither an
     * array nor a Traversable, or if any of the $values are not a legal value.
     */
    protected function doSetMultiple($values, $ttl = null): bool
    {
        $this->checkKeys($values);

        $result = true;
        foreach ($values as $key => $value) {
            if (true !== $this->set($key, $value, $ttl)) {
                $result = false;
            }
        }

        return $result;
    }


    /**
     * Returns the exception class that will be used for exceptions thrown
     *
     * @return string
     */
    abstract protected function getExceptionClass(): string;
}
