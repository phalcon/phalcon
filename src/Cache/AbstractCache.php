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
use Phalcon\Events\EventsAwareInterface;
use Phalcon\Events\Traits\EventsAwareTrait;
use Redis as RedisService;
use Throwable;
use Traversable;

/**
 * This component offers caching capabilities for your application.
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
     */
    public function __construct(
        protected AdapterInterface $adapter
    ) {
    }

    /**
     * Fetches a value from the cache.
     */
    abstract public function get(string $key, mixed $defaultValue = null): mixed;

    /**
     * Returns the current adapter
     */
    public function getAdapter(): AdapterInterface
    {
        return $this->adapter;
    }

    /**
     * Persists data in the cache, uniquely referenced by a key with an
     * optional expiration TTL time.
     */
    abstract public function set(
        string $key,
        mixed $value,
        DateInterval | int | null $ttl = null
    ): bool;

    /**
     * Checks the key. If it contains invalid characters an exception is thrown
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
     */
    protected function doClear(): bool
    {
        return $this->adapter->clear();
    }

    /**
     * Delete an item from the cache by its unique key.
     */
    protected function doDelete(string $key): bool
    {
        $this->checkKey($key);

        $this->fireManagerEvent("cache:beforeDelete", $key);

        $result = $this->adapter->delete($key);

        $this->fireManagerEvent("cache:afterDelete", $key);

        return $result;
    }

    /**
     * Deletes multiple cache items in a single operation.
     *
     * @phpstan-param iterable<array-key, string> $keys
     */
    protected function doDeleteMultiple(iterable $keys): bool
    {
        $this->checkKeys($keys);

        $this->fireManagerEvent("cache:beforeDeleteMultiple", $keys);

        $keysArray = [];
        /** @var string $key */
        foreach ($keys as $key) {
            $this->checkKey($key);
            $keysArray[] = $key;
        }

        $result = $this->adapter->deleteMultiple($keysArray);

        $this->fireManagerEvent("cache:afterDeleteMultiple", $keys);

        return $result;
    }

    /**
     * Fetches a value from the cache.
     */
    protected function doGet(string $key, mixed $defaultValue = null): mixed
    {
        $this->checkKey($key);

        $this->fireManagerEvent("cache:beforeGet", $key);

        $result = $this->adapter->get($key, $defaultValue);

        $this->fireManagerEvent("cache:afterGet", $key);

        return $result;
    }

    /**
     * Obtains multiple cache items by their unique keys.
     *
     * @phpstan-param iterable<array-key, string> $keys
     *
     * @phpstan-return array<string, mixed>
     */
    protected function doGetMultiple(iterable $keys, mixed $defaultValue = null): iterable
    {
        $this->checkKeys($keys);

        $this->fireManagerEvent("cache:beforeGetMultiple", $keys);

        /**
         * The adapter is read into a local variable because calls such as
         * `checkKey()` below discard the type narrowed by `instanceof`.
         */
        $adapter = $this->adapter;
        if ($adapter instanceof Redis) {
            /**
             * Validate every key and collect them into an array (this also
             * handles Traversable inputs), so `mget()` and `array_combine()`
             * below receive arrays instead of throwing a TypeError.
             *
             * NOTE: incoming keys are not routed through the adapter's key
             * policy here - `getKeyWithoutPrefix()` is protected on the
             * Storage adapter, so an already-prefixed key is prefixed again by
             * the phpredis `OPT_PREFIX` and misses. Resolving that needs the
             * batch-capability redesign noted in the modularity review.
             */
            $keysArray = [];
            /** @var string $element */
            foreach ($keys as $element) {
                $this->checkKey($element);
                $keysArray[] = $element;
            }

            $serializer = $adapter->getSerializer();
            /** @var RedisService $connection */
            $connection = $adapter->getAdapter();
            /** @var array<array-key, mixed> $results */
            $results = $connection->mget($keysArray);
            $results = array_map(
                function ($element) use ($serializer, $defaultValue) {
                    if (false === $element) {
                        return $defaultValue;
                    }

                    /**
                     * No serializer means the raw value is returned, the same
                     * as the Storage adapters do.
                     */
                    if (null === $serializer) {
                        return $element;
                    }

                    $serializer->unserialize($element);

                    if (
                        true === method_exists($serializer, "isSuccess") &&
                        true !== $serializer->isSuccess()
                    ) {
                        return $defaultValue;
                    }

                    return $serializer->getData();
                },
                $results
            );
            $results = array_combine($keysArray, $results);
        } else {
            $results = [];
            /** @var string $element */
            foreach ($keys as $element) {
                $results[$element] = $this->get($element, $defaultValue);
            }
        }

        $this->fireManagerEvent("cache:afterGetMultiple", $keys);

        return $results;
    }

    /**
     * Determines whether an item is present in the cache.
     */
    protected function doHas(string $key): bool
    {
        $this->checkKey($key);

        $this->fireManagerEvent("cache:beforeHas", $key);

        $result = $this->adapter->has($key);

        $this->fireManagerEvent("cache:afterHas", $key);

        return $result;
    }

    /**
     * Persists data in the cache, uniquely referenced by a key with an optional
     * expiration TTL time.
     */
    protected function doSet(
        string $key,
        mixed $value,
        DateInterval | int | null $ttl = null
    ): bool {
        $this->checkKey($key);

        $this->fireManagerEvent("cache:beforeSet", $key);

        $result = $this->adapter->set($key, $value, $ttl);

        $this->fireManagerEvent("cache:afterSet", $key);

        return $result;
    }

    /**
     * Persists a set of key => value pairs in the cache, with an optional TTL.
     *
     * @phpstan-param iterable<string, mixed> $values
     * @phpstan-param DateInterval|int|null   $ttl
     */
    protected function doSetMultiple(iterable $values, mixed $ttl = null): bool
    {
        $this->checkKeys($values);

        $keys = array_keys((array)$values);
        foreach ($keys as $key) {
            $this->checkKey($key);
        }

        $this->fireManagerEvent("cache:beforeSetMultiple", $keys);

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

        $this->fireManagerEvent("cache:afterSetMultiple", $keys);

        return $result;
    }

    /**
     * Returns the exception class that will be used for exceptions thrown
     *
     * @return class-string<Throwable>
     */
    abstract protected function getExceptionClass(): string;
}
