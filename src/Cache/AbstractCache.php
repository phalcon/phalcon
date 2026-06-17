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
use Phalcon\Cache\Exception\InvalidArgumentException;
use Phalcon\Events\EventsAwareInterface;
use Phalcon\Events\Exception as EventsException;
use Phalcon\Events\ManagerInterface;
use Phalcon\Events\Traits\EventsAwareTrait;
use Traversable;

/**
 * This component offers caching capabilities for your application.
 * Phalcon\Cache implements PSR-16.
 *
 * Event layering: cache operations can emit `cache:*` events from two layers.
 * This facade fires `cache:before*`/`cache:after*` around each operation, and
 * the underlying `Storage` adapter (whose `eventType` is `"cache"`) also fires
 * `cache:before*`/`cache:after*` for the same operation. If an events manager
 * is wired into both the facade and the adapter, a single call emits the event
 * twice (once from each object). Wire the manager into one layer only; the
 * facade is the supported source for cache-level events (it also emits the
 * multi-key `cache:*Multiple` events).
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
     * Fetches a value from the cache.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    abstract public function get(string $key, mixed $default = null): mixed;

    /**
     * Persists data in the cache, uniquely referenced by a key with an
     * optional expiration TTL time.
     *
     * @param string                $key
     * @param mixed                 $value
     * @param null|int|DateInterval $ttl
     *
     * @return bool
     */
    abstract public function set(
        string $key,
        mixed $value,
        null | int | DateInterval $ttl = null
    ): bool;

    /**
     * Checks the key. If it contains invalid characters an exception is thrown
     *
     * @param string $key
     *
     * @throws Exception
     */
    protected function checkKey(string $key): void
    {
        if ("" === $key || preg_match("/[^A-Za-z0-9-_.]/", $key)) {
            $exceptionClass = $this->getExceptionClass();

            throw new $exceptionClass("The key contains invalid characters");
        }
    }

    /**
     * Checks the key. If it contains invalid characters an exception is thrown
     *
     * @param mixed $keys
     *
     * @throws InvalidArgumentException
     */
    protected function checkKeys(mixed $keys): void
    {
        if (!is_array($keys) && !($keys instanceof Traversable)) {
            $exceptionClass = $this->getExceptionClass();

            throw new $exceptionClass(
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
     * @throws Exception MUST be thrown if the $key string is
     *                                  not a legal value.
     * @throws EventsException
     */
    protected function doDelete(string $key): bool
    {
        $this->checkKey($key);

        $this->fire("cache:beforeDelete", $key);

        $result = $this->adapter->delete($key);

        $this->fire("cache:afterDelete", $key);

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
        $this->checkKeys($keys);

        $this->fire("cache:beforeDeleteMultiple", $keys);

        $keysArray = [];
        /** @var string $key */
        foreach ($keys as $key) {
            $this->checkKey($key);
            $keysArray[] = $key;
        }

        $result = $this->adapter->deleteMultiple($keysArray);

        $this->fire("cache:afterDeleteMultiple", $keys);

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
        $this->checkKey($key);

        $this->fire("cache:beforeGet", $key);

        $result = $this->adapter->get($key, $default);

        $this->fire("cache:afterGet", $key);

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
    protected function doGetMultiple(iterable $keys, mixed $default = null): iterable
    {
        $this->checkKeys($keys);

        $this->fire("cache:beforeGetMultiple", $keys);

        if ($this->adapter instanceof Redis) {
            /**
             * Validate every key and collect them into an array (this also
             * handles Traversable inputs), so mget() and array_combine() below
             * receive arrays instead of throwing a TypeError.
             *
             * NOTE: incoming keys are not routed through the adapter's key
             * policy here - getKeyWithoutPrefix() is protected on the Storage
             * adapter, so an already-prefixed key is prefixed again by the
             * phpredis OPT_PREFIX and misses. Resolving that needs the
             * batch-capability redesign noted in the modularity review.
             */
            $keysArray = [];
            /** @var string $element */
            foreach ($keys as $element) {
                $this->checkKey($element);
                $keysArray[] = $element;
            }

            $serializer = $this->adapter->getSerializer();
            $results    = $this->adapter->getAdapter()->mget($keysArray);
            $results    = array_map(
                function ($element) use ($serializer, $default) {
                    if (false === $element) {
                        return $default;
                    }

                    $serializer->unserialize($element);

                    if (
                        true === method_exists($serializer, "isSuccess") &&
                        true !== $serializer->isSuccess()
                    ) {
                        return $default;
                    }

                    return $serializer->getData();
                },
                $results
            );
            $results    = array_combine($keysArray, $results);
        } else {
            $results = [];
            /** @var string $element */
            foreach ($keys as $element) {
                $results[$element] = $this->get($element, $default);
            }
        }

        $this->fire("cache:afterGetMultiple", $keys);

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
        $this->checkKey($key);

        $this->fire("cache:beforeHas", $key);

        $result = $this->adapter->has($key);

        $this->fire("cache:afterHas", $key);

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
        $this->checkKey($key);

        $this->fire("cache:beforeSet", $key);

        $result = $this->adapter->set($key, $value, $ttl);

        $this->fire("cache:afterSet", $key);

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
    protected function doSetMultiple(iterable $values, mixed $ttl = null): bool
    {
        $this->checkKeys($values);

        $keys = array_keys((array)$values);
        foreach ($keys as $key) {
            $this->checkKey($key);
        }

        $this->fire("cache:beforeSetMultiple", $keys);

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

        $this->fire("cache:afterSetMultiple", $keys);

        return $result;
    }

    /**
     * Trigger an event for the eventsManager.
     *
     * @param string $eventName
     * @param mixed  $keys
     */
    protected function fire(string $eventName, mixed $keys): void
    {
        if (null === $this->eventsManager) {
            return;
        }

        $this->eventsManager->fire($eventName, $this, $keys, false);
    }

    /**
     * Returns the exception class that will be used for exceptions thrown
     *
     * @return string
     */
    abstract protected function getExceptionClass(): string;
}
