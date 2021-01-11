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
use Phalcon\Storage\Serializer\SerializerInterface;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Support\Exception as SupportException;
use Phalcon\Support\HelperFactory;

use function is_object;
use function mb_strtolower;

/**
 * Class AbstractAdapter
 *
 * @package Phalcon\Storage\Adapter
 *
 * @property mixed               $adapter
 * @property string              $defaultSerializer
 * @property HelperFactory       $helperFactory
 * @property int                 $lifetime
 * @property array               $options
 * @property string              $prefix
 * @property SerializerInterface $serializer
 * @property SerializerFactory   $serializerFactory
 */
abstract class AbstractAdapter implements AdapterInterface
{
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
     * Serializer Factory
     *
     * @var SerializerFactory
     */
    protected SerializerFactory $serializerFactory;

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
    protected ?SerializerInterface $serializer;

    /**
     * Helper Factory
     * @var HelperFactory
     */
    protected HelperFactory $helperFactory;

    /**
     * AbstractAdapter constructor.
     *
     * @param SerializerFactory $factory
     * @param array             $options
     */
    protected function __construct(
        HelperFactory $helperFactory,
        SerializerFactory $factory,
        array $options = []
    ) {
        /**
         * Lets set some defaults and options here
         */
        $this->helperFactory     = $helperFactory;
        $this->serializerFactory = $factory;
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
     * @return int | bool
     */
    abstract public function decrement(string $key, int $value = 1);

    /**
     * Deletes data from the adapter
     *
     * @param string $key
     *
     * @return bool
     */
    abstract public function delete(string $key): bool;

    /**
     * Reads data from the adapter
     *
     * @param string     $key
     * @param mixed|null $defaultValue
     *
     * @return mixed
     */
    abstract public function get(string $key, $defaultValue = null);

    /**
     * Returns the adapter - connects to the storage if not connected
     *
     * @return mixed
     */
    public function getAdapter()
    {
        return $this->adapter;
    }


    /**
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
     * Returns the prefix
     *
     * @return string
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }

    /**
     * Checks if an element exists in the cache
     *
     * @param string $key
     *
     * @return bool
     */
    abstract public function has(string $key): bool;

    /**
     * Increments a stored number
     *
     * @param string $key
     * @param int    $value
     *
     * @return int | bool
     */
    abstract public function increment(string $key, int $value = 1);

    /**
     * Stores data in the adapter
     *
     * @param string                $key
     * @param mixed                 $value
     * @param DateInterval|int|null $ttl
     *
     * @return bool
     */
    abstract public function set(string $key, $value, $ttl = null): bool;

    /**
     * @param string $serializer
     */
    public function setDefaultSerializer(string $serializer): void
    {
        $this->defaultSerializer = mb_strtolower($serializer);
    }

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
            if (true === $this->helperFactory->startsWith($key, $needle)) {
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
    protected function getPrefixedKey($key): string
    {
        return $this->prefix . ((string) $key);
    }

    /**
     * Returns serialized data
     *
     * @param mixed $content
     *
     * @return mixed
     */
    protected function getSerializedData($content)
    {
        if ('' !== $this->defaultSerializer) {
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
    protected function getTtl($ttl): int
    {
        if (null === $ttl) {
            return $this->lifetime;
        }

        if (is_object($ttl) && $ttl instanceof DateInterval) {
            $dateTime = new DateTime('@0');
            return $dateTime->add($ttl)->getTimestamp();
        }

        return (int) $ttl;
    }

    /**
     * Returns unserialized data
     *
     * @param mixed      $content
     * @param mixed|null $defaultValue
     *
     * @return mixed
     */
    protected function getUnserializedData($content, $defaultValue = null)
    {
        if (!$content) {
            return $defaultValue;
        }

        if ('' !== $this->defaultSerializer) {
            $this->serializer->unserialize($content);
            $content = $this->serializer->getData();
        }

        return $content;
    }

    /**
     * Initializes the serializer
     *
     * @throws SupportException
     */
    protected function initSerializer(): void
    {
        if (
            true !== empty($this->defaultSerializer) &&
            true !== is_object($this->serializer)
        ) {
            $className        = $this->defaultSerializer;
            $this->serializer = $this->serializerFactory->newInstance($className);
        }
    }
}
