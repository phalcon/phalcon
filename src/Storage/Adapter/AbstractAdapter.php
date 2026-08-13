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

namespace Phalcon\Storage\Adapter;

use DateInterval;
use DateTime;
use Exception;
use Phalcon\Contracts\Storage\StorageTypes;
use Phalcon\Events\EventsAwareInterface;
use Phalcon\Events\Traits\EventsAwareTrait;
use Phalcon\Storage\Serializer\SerializerInterface;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Traits\Support\Helper\Arr\GetTrait;

use function is_object;
use function mb_strtolower;
use function method_exists;

/**
 * Storage AbstractAdapter
 *
 * @phpstan-import-type storage_keys from StorageTypes
 * @phpstan-import-type storage_options from StorageTypes
 */
abstract class AbstractAdapter implements AdapterInterface, EventsAwareInterface
{
    use EventsAwareTrait;
    use GetTrait;

    /**
     * @var mixed
     */
    protected mixed $adapter = null;

    /**
     * Name of the default serializer class
     */
    protected string $defaultSerializer = 'php';

    /**
     * EventType prefix.
     *
     * @var string
     */
    protected string $eventType = "storage";

    /**
     * Name of the default TTL (time to live)
     */
    protected int $lifetime = 3600;

    /**
     * @var array<string, mixed>
     *
     * @phpstan-var storage_options
     */
    protected array $options = [];
    protected string $prefix = 'ph-memo-';
    protected SerializerInterface | null $serializer = null;
    /**
     * Whether a leading prefix is stripped from incoming keys before the
     * adapter prefix is applied. Disable when keys are externally
     * generated identifiers that may legitimately start with the prefix
     * text (e.g. session ids).
     *
     * @var bool
     */
    protected bool $stripPrefix = true;

    /**
     * AbstractAdapter constructor.
     *
     * @phpstan-param storage_options $options
     */
    protected function __construct(
        protected SerializerFactory $serializerFactory,
        array $options = []
    ) {
        /** @var string $defaultSerializer */
        $defaultSerializer = $this->getArrVal($options, 'defaultSerializer', 'php');
        /** @var int $lifetime */
        $lifetime = $this->getArrVal($options, 'lifetime', 3600);
        /** @var SerializerInterface|null $serializer */
        $serializer = $this->getArrVal($options, 'serializer', null);

        /**
         * Lets set some defaults and options here
         */
        $this->defaultSerializer = mb_strtolower($defaultSerializer);
        $this->lifetime          = $lifetime;
        $this->serializer        = $serializer;
        $this->stripPrefix       = (bool) $this->getArrVal($options, 'stripPrefix', true);

        if (isset($options['prefix'])) {
            /** @var string $prefix */
            $prefix       = $options['prefix'];
            $this->prefix = $prefix;
        }

        unset(
            $options['defaultSerializer'],
            $options['lifetime'],
            $options['serializer'],
            $options['prefix'],
            $options['stripPrefix']
        );

        $this->options = $options;
    }

    /**
     * Flushes/clears the cache
     */
    abstract public function clear(): bool;

    /**
     * Decrements a stored number
     */
    public function decrement(string $key, int $value = 1): false | int
    {
        $key = $this->getKeyWithoutPrefix($key);

        $this->fireManagerEvent($this->eventType . ":beforeDecrement", $key);

        $result = $this->doDecrement($key, $value);

        $this->fireManagerEvent($this->eventType . ":afterDecrement", $key);

        return $result;
    }

    /**
     * Deletes data from the adapter
     */
    public function delete(string $key): bool
    {
        $key = $this->getKeyWithoutPrefix($key);

        $this->fireManagerEvent($this->eventType . ":beforeDelete", $key);

        $result = $this->doDelete($key);

        $this->fireManagerEvent($this->eventType . ":afterDelete", $key);

        return $result;
    }

    /**
     * Deletes multiple data from the adapter
     *
     * @phpstan-param storage_keys $keys
     */
    public function deleteMultiple(array $keys): bool
    {
        $filteredKeys = [];
        foreach ($keys as $key) {
            $filteredKeys[] = $this->getKeyWithoutPrefix($key);
        }

        $keys = $filteredKeys;

        $this->fireManagerEvent($this->eventType . ":beforeDeleteMultiple", $keys);

        $result = $this->doDeleteMultiple($keys);

        $this->fireManagerEvent($this->eventType . ":afterDeleteMultiple", $keys);

        return $result;
    }

    /**
     * Reads data from the adapter
     */
    public function get(string $key, mixed $defaultValue = null): mixed
    {
        $key = $this->getKeyWithoutPrefix($key);

        $this->fireManagerEvent($this->eventType . ":beforeGet", $key);

        $result = $this->doGet($key, $defaultValue);

        $this->fireManagerEvent($this->eventType . ":afterGet", $key);

        return $result;
    }

    /**
     * Returns the adapter - connects to the storage if not connected
     */
    public function getAdapter(): mixed
    {
        return $this->adapter;
    }

    /**
     * Name of the default serializer class
     */
    public function getDefaultSerializer(): string
    {
        return $this->defaultSerializer;
    }

    /**
     * Returns all the keys stored
     *
     * @phpstan-return storage_keys
     */
    abstract public function getKeys(string $prefix = ''): array;

    /**
     * Returns the lifetime
     */
    public function getLifetime(): int
    {
        return $this->lifetime;
    }

    /**
     * Returns the prefix
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }

    /**
     * Get the serializer
     */
    public function getSerializer(): SerializerInterface | null
    {
        return $this->serializer;
    }

    /**
     * Checks if an element exists in the cache
     */
    public function has(string $key): bool
    {
        $key = $this->getKeyWithoutPrefix($key);

        $this->fireManagerEvent($this->eventType . ":beforeHas", $key);

        $result = $this->doHas($key);

        $this->fireManagerEvent($this->eventType . ":afterHas", $key);

        return $result;
    }

    /**
     * Increments a stored number
     */
    public function increment(string $key, int $value = 1): false | int
    {
        $key = $this->getKeyWithoutPrefix($key);

        $this->fireManagerEvent($this->eventType . ":beforeIncrement", $key);

        $result = $this->doIncrement($key, $value);

        $this->fireManagerEvent($this->eventType . ":afterIncrement", $key);

        return $result;
    }

    /**
     * Stores data in the adapter. If the TTL is `null` (default) or not defined
     * then the default TTL will be used, as set in this adapter. If the TTL
     * is `0` or a negative number, a `delete()` will be issued, since this
     * item has expired. If you need to set this key forever, you should use
     * the `setForever()` method.
     */
    public function set(string $key, mixed $value, mixed $ttl = null): bool
    {
        $key = $this->getKeyWithoutPrefix($key);

        $this->fireManagerEvent($this->eventType . ":beforeSet", $key);

        $result = $this->doSet($key, $value, $ttl);

        $this->fireManagerEvent($this->eventType . ":afterSet", $key);

        return $result;
    }

    /**
     * @param string $serializer
     */
    public function setDefaultSerializer(string $serializer): void
    {
        $this->defaultSerializer = mb_strtolower($serializer);
    }

    /**
     * Decrements a stored number
     */
    abstract protected function doDecrement(string $key, int $value = 1): false | int;

    /**
     * Deletes data from the adapter
     */
    abstract protected function doDelete(string $key): bool;

    /**
     * Deletes multiple data from the adapter
     *
     * @phpstan-param storage_keys $keys
     */
    protected function doDeleteMultiple(array $keys): bool
    {
        $result = true;
        foreach ($keys as $key) {
            if (true !== $this->doDelete($key)) {
                $result = false;
            }
        }

        return $result;
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    protected function doGet(string $key, mixed $defaultValue = null): mixed
    {
        if (true !== $this->doHas($key)) {
            return $defaultValue;
        }

        $content = $this->doGetData($key);

        return $this->getUnserializedData($content, $defaultValue);
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    protected function doGetData(string $key): mixed
    {
        /**
         * Every adapter that relies on this implementation is backed by a
         * client exposing `get()`; the rest override `doGet()`/`doGetData()`.
         *
         * @var \Memcached|\Redis|\RedisCluster $adapter
         */
        $adapter = $this->getAdapter();

        return $adapter->get($key);
    }

    /**
     * Checks if an element exists in the cache
     *
     * @param string $key
     *
     * @return bool
     */
    abstract protected function doHas(string $key): bool;

    /**
     * Increments a stored number
     */
    abstract protected function doIncrement(string $key, int $value = 1): false | int;

    /**
     * Stores data in the adapter. If the TTL is `null` (default) or not defined
     * then the default TTL will be used, as set in this adapter. If the TTL
     * is `0` or a negative number, a `delete()` will be issued, since this
     * item has expired. If you need to set this key forever, you should use
     * the `setForever()` method.
     *
     * @param string                $key
     * @param mixed                 $value
     * @param DateInterval|int|null $ttl
     *
     * @return bool
     */
    abstract protected function doSet(string $key, mixed $value, mixed $ttl = null): bool;

    /**
     * Filters the keys array based on global and passed prefix
     *
     * @phpstan-param storage_keys|false $keys
     *
     * @phpstan-return storage_keys
     */
    protected function getFilteredKeys($keys, string $prefix): array
    {
        $results = [];
        $needle  = $this->prefix . $prefix;
        $keys    = !$keys ? [] : $keys;

        foreach ($keys as $key) {
            if (str_starts_with($key, $needle)) {
                $results[] = $key;
            }
        }

        return $results;
    }

    /**
     * Check if the key has the prefix and remove it, otherwise just return the
     * key unaltered. When the `stripPrefix` option is `false` the key is
     * always returned unaltered.
     */
    protected function getKeyWithoutPrefix(string $key): string
    {
        if ($this->stripPrefix && str_starts_with($key, $this->prefix)) {
            return substr($key, strlen($this->prefix));
        }

        return $key;
    }

    /**
     * Returns the key requested, prefixed
     *
     * @param float|int|string $key
     */
    protected function getPrefixedKey(mixed $key): string
    {
        return $this->prefix . $this->getKeyWithoutPrefix((string)$key);
    }

    /**
     * Returns serialized data
     *
     * @throws Exception
     */
    protected function getSerializedData(mixed $content): mixed
    {
        if (null !== $this->serializer) {
            $this->serializer->setData($content);
            $content = $this->serializer->serialize();
        }

        return $content;
    }

    /**
     * Calculates the TTL for a cache item
     *
     * @throws Exception
     */
    protected function getTtl(mixed $ttl): int
    {
        if (null === $ttl) {
            return $this->lifetime;
        }

        if (is_object($ttl) && $ttl instanceof DateInterval) {
            $dateTime = new DateTime('@0');
            return $dateTime->add($ttl)->getTimestamp();
        }

        /** @var float|int|string $ttl */
        return (int)$ttl;
    }

    /**
     * Returns unserialized data
     */
    protected function getUnserializedData(
        mixed $content,
        mixed $defaultValue = null
    ): mixed {
        if (null !== $this->serializer) {
            $this->serializer->unserialize($content);

            if (
                true === method_exists($this->serializer, 'isSuccess') &&
                true !== $this->serializer->isSuccess()
            ) {
                return $defaultValue;
            }

            $content = $this->serializer->getData();
        }

        return $content;
    }

    /**
     * Initializes the serializer
     *
     * @throws Exception
     */
    protected function initSerializer(): void
    {
        if (
            !empty($this->defaultSerializer) &&
            !is_object($this->serializer)
        ) {
            $className        = $this->defaultSerializer;
            $this->serializer = $this->serializerFactory->newInstance($className);
        }
    }
}
