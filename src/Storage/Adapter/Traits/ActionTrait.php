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

namespace Phalcon\Storage\Adapter\Traits;

use DateInterval;
use DateTime;
use Exception;
use Phalcon\Storage\Adapter\AdapterInterface;
use Phalcon\Storage\Exception as StorageException;
use Phalcon\Storage\Serializer\SerializerInterface;
use Phalcon\Storage\SerializerFactory;

use function is_object;

/**
 * Trait for the `do*` methods
 *
 * @method bool             has(string $key)
 * @method AdapterInterface getAdapter()
 *
 * @property string                   $defaultSerializer
 * @property int                      $lifetime
 * @property string                   $prefix
 * @property SerializerInterface|null $serializer;
 * @property SerializerFactory        $serializerFactory
 */
trait ActionTrait
{
    /**
     * Delete multiple keys.
     *
     * @param iterable $keys
     *
     * @return bool
     * @throws StorageException
     */
    public function doDeleteMultiple(iterable $keys): bool
    {
        $result = true;
        foreach ($keys as $key) {
            /**
             * Only set this to false if something went wrong
             */
            if (false === $this->doDelete($key)) {
                $result = false;
            }
        }

        return $result;
    }

    /**
     * Get multiple keys. Returns an array success, false on failure. Raises
     * an EventsException if the event cannot be processed, and an Exception if
     * any of the keys have wrong names.
     *
     * @param iterable   $keys
     * @param mixed|null $defaultValue
     *
     * @return array<string, mixed>
     */
    public function doGetMultiple(
        iterable $keys,
        mixed $defaultValue = null
    ): array {
        $results = [];
        /** @var string $element */
        foreach ($keys as $element) {
            $results[$element] = $this->get($element, $defaultValue);
        }

        return $results;
    }

    /**
     * Sets multiple keys. Returns true on success, false on failure.
     *
     * @param iterable              $values
     * @param DateInterval|int|null $ttl
     *
     * @return bool
     * @throws StorageException
     */
    public function doSetMultiple(
        iterable $values,
        DateInterval | int | null $ttl = null
    ): bool {
        $result = true;
        /**
         * @var string $key
         * @var mixed  $value
         */
        foreach ($values as $key => $value) {
            $this->assertKey($key);

            if (true !== $this->doSet($key, $value, $ttl)) {
                $result = false;
            }
        }

        return $result;
    }

    /**
     * Decrements a stored number
     *
     * @param string $key
     * @param int    $value
     *
     * @return false|int
     */
    protected function doDecrement(string $key, int $value = 1): false | int
    {
        $this->assertKey($key);
    }

    /**
     * Deletes data from the adapter. This just checks the key and is called
     * from the children
     *
     * @param string $key
     *
     * @return bool
     */
    protected function doDelete(string $key): bool
    {
        $this->assertKey($key);

        return true;
    }

    /**
     * @param string     $key
     * @param mixed|null $defaultValue
     *
     * @return mixed
     */
    protected function doGet(string $key, mixed $defaultValue = null): mixed
    {
        $content = $this->doGetData($key);

        if (false === $content) {
            return $defaultValue;
        }

        return $this->getUnserializedData($content, $defaultValue);
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    protected function doGetData(string $key): mixed
    {
        return $this->getAdapter()->get($key);
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
     *
     * @param string $key
     * @param int    $value
     *
     * @return false|int
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
    abstract protected function doSet(
        string $key,
        mixed $value,
        DateInterval | int | null $ttl = null
    ): bool;

    /**
     * Filters the keys array based on global and passed prefix
     *
     * @param mixed  $keys
     * @param string $prefix
     *
     * @return array
     */
    protected function getFilteredKeys(array $keys, string $prefix): array
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
     * Returns the key requested, prefixed
     *
     * @param string $key
     *
     * @return string
     */
    protected function getPrefixedKey(mixed $key): string
    {
        return $this->prefix . ((string)$key);
    }

    /**
     * Returns serialized data
     *
     * @param mixed $content
     *
     * @return mixed|string|null
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
     * @param DateInterval|int|null $ttl
     *
     * @return int
     * @throws Exception
     */
    protected function getTtl(mixed $ttl): int
    {
        if (null === $ttl) {
            return $this->lifetime;
        }

        if ($ttl instanceof DateInterval) {
            $dateTime = new DateTime('@0');
            return $dateTime->add($ttl)
                            ->getTimestamp()
            ;
        }

        return (int)$ttl;
    }

    /**
     * Returns unserialized data
     *
     * @param mixed      $content
     * @param mixed|null $defaultValue
     *
     * @return mixed
     */
    protected function getUnserializedData(
        mixed $content,
        mixed $defaultValue = null
    ): mixed {
        if (null !== $this->serializer) {
            $this->serializer->unserialize($content);

            if (true !== $this->serializer->isSuccess()) {
                return $defaultValue;
            }

            $content = $this->serializer->getData();
        }

        return $content;
    }

    /**
     * Initializes the serializer
     *
     * @return void
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
