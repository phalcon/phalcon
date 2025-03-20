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
use Phalcon\Events\EventsAwareInterface;
use Phalcon\Events\Traits\EventsAwareTrait;
use Phalcon\Storage\Serializer\SerializerInterface;
use Phalcon\Storage\SerializerFactory;

use function is_object;
use function mb_strtolower;

/**
 * Class AbstractAdapter
 *
 * @package Phalcon\Storage\Adapter
 *
 * @property mixed               $adapter
 * @property string              $defaultSerializer
 * @property int                 $lifetime
 * @property array               $options
 * @property string              $prefix
 * @property SerializerInterface $serializer
 * @property SerializerFactory   $serializerFactory
 */
abstract class AbstractAdapter implements AdapterInterface, EventsAwareInterface
{
    use EventsAwareTrait;

    /**
     * @var mixed
     */
    protected $adapter;

    /**
     * Name of the default serializer class
     *
     * @var string
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
     *
     * @var int
     */
    protected int $lifetime = 3600;

    /**
     * @var array
     */
    protected array $options = [];

    /**
     * @var string
     */
    protected string $prefix = 'ph-memo-';

    /**
     * Serializer
     *
     * @var SerializerInterface|null
     */
    protected SerializerInterface | null $serializer;

    /**
     * AbstractAdapter constructor.
     *
     * @param SerializerFactory $serializerFactory
     * @param array             $options
     */
    protected function __construct(
        protected SerializerFactory $serializerFactory,
        array $options = []
    ) {
        /**
         * Lets set some defaults and options here
         */
        $this->defaultSerializer = mb_strtolower(($options['defaultSerializer']) ?? 'php');
        $this->lifetime          = $options['lifetime'] ?? 3600;
        $this->serializer        = $options['serializer'] ?? null;

        if (isset($options['prefix'])) {
            $this->prefix = $options['prefix'];
        }

        unset(
            $options['defaultSerializer'],
            $options['lifetime'],
            $options['serializer'],
            $options['prefix']
        );

        $this->options = $options;
    }

    /**
     * Flushes/clears the cache
     *
     * @return bool
     */
    abstract public function clear(): bool;

    /**
     * Decrements a stored number
     *
     * @param string $key
     * @param int    $value
     *
     * @return false|int
     */
    public function decrement(string $key, int $value = 1): false | int
    {
        $this->fireManagerEvent($this->eventType . ":beforeDecrement", $key);

        $result = $this->doDecrement($key, $value);

        $this->fireManagerEvent($this->eventType . ":afterDecrement", $key);

        return $result;
    }

    /**
     * Deletes data from the adapter
     *
     * @param string $key
     *
     * @return bool
     */
    public function delete(string $key): bool
    {
        $this->fireManagerEvent($this->eventType . ":beforeDelete", $key);

        $result = $this->doDelete($key);

        $this->fireManagerEvent($this->eventType . ":afterDelete", $key);

        return $result;
    }

    /**
     * Reads data from the adapter
     *
     * @param string     $key
     * @param mixed|null $defaultValue
     *
     * @return mixed|null
     */
    public function get(string $key, mixed $defaultValue = null): mixed
    {
        $this->fireManagerEvent($this->eventType . ":beforeGet", $key);

        $result = $this->doGet($key, $defaultValue);

        $this->fireManagerEvent($this->eventType . ":afterGet", $key);

        return $result;
    }

    /**
     * Returns the adapter - connects to the storage if not connected
     *
     * @return mixed
     */
    public function getAdapter(): mixed
    {
        return $this->adapter;
    }

    /**
     * Name of the default serializer class
     *
     * @return string
     */
    public function getDefaultSerializer(): string
    {
        return $this->defaultSerializer;
    }

    /**
     * Returns all the keys stored
     *
     * @param string $prefix
     *
     * @return array
     */
    abstract public function getKeys(string $prefix = ''): array;

    /**
     * Returns the lifetime
     *
     * @return int
     */
    public function getLifetime(): int
    {
        return $this->lifetime;
    }

    /**
     * Returns the prefix
     *
     * @return string
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }

    /**
     * Get the serializer
     *
     * @return SerializerInterface
     */
    public function getSerializer(): SerializerInterface
    {
        return $this->serializer;
    }

    /**
     * Checks if an element exists in the cache
     *
     * @param string $key
     *
     * @return bool
     */
    public function has(string $key): bool
    {
        $this->fireManagerEvent($this->eventType . ":beforeHas", $key);

        $result = $this->doHas($key);

        $this->fireManagerEvent($this->eventType . ":afterHas", $key);

        return $result;
    }

    /**
     * Increments a stored number
     *
     * @param string $key
     * @param int    $value
     *
     * @return false|int
     */
    public function increment(string $key, int $value = 1): false | int
    {
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
     *
     * @param string                $key
     * @param mixed                 $value
     * @param DateInterval|int|null $ttl
     *
     * @return bool
     */
    public function set(string $key, mixed $value, mixed $ttl = null): bool
    {
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
     *
     * @param string $key
     * @param int    $value
     *
     * @return false|int
     */
    abstract protected function doDecrement(string $key, int $value = 1): false | int;

    /**
     * Deletes data from the adapter
     *
     * @param string $key
     *
     * @return bool
     */
    abstract protected function doDelete(string $key): bool;

    /**
     * @param string $key
     *
     * @return mixed
     */
    protected function doGet(string $key, mixed $defaultValue = null): mixed
    {
        if (true !== $this->has($key)) {
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
    abstract protected function doSet(string $key, mixed $value, mixed $ttl = null): bool;

    /**
     * Filters the keys array based on global and passed prefix
     *
     * @param mixed  $keys
     * @param string $prefix
     *
     * @return array
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

        if (is_object($ttl) && $ttl instanceof DateInterval) {
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
