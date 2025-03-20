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
use Exception as BaseException;
use Phalcon\Storage\SerializerFactory;
use WeakReference;

use function is_int;
use function is_object;

/**
 * Weak Adapter
 */
class Weak extends AbstractAdapter
{
    /**
     * @var string|null
     */
    protected string | null $fetching = null;

    /**
     * @var array
     */

    protected array $weakList = [];

    /**
     * Constructor, there are no options
     *
     * @param SerializerFactory $factory
     * @param array             $options
     */
    public function __construct(
        SerializerFactory $factory,
        protected array $options = []
    ) {
        parent::__construct($factory, $options);

        $this->defaultSerializer = "none";
        $this->prefix            = "";
    }

    /**
     * Flushes/clears the cache
     */
    public function clear(): bool
    {
        $this->weakList = [];

        return true;
    }

    /**
     * Stores data in the adapter
     *
     * @param string $prefix
     *
     * @return array
     */
    public function getKeys(string $prefix = ""): array
    {
        $keys = array_keys($this->weakList);
        if ('' !== $prefix) {
            $results = [];
            foreach ($keys as $key) {
                if (str_starts_with($key, $prefix)) {
                    $results[] = $key;
                }
            }

            return $results;
        }

        return $keys;
    }

    /**
     * Will never set a serializer, WeakReference cannot be serialized
     *
     * @param string $serializer
     */
    public function setDefaultSerializer(string $serializer): void
    {
    }

    /**
     * For compatiblity only, there is no Forever with WeakReference.
     *
     * @param string $key
     * @param mixed  $data
     *
     * @return bool
     */
    public function setForever(string $key, mixed $data): bool
    {
        return $this->set($key, $data);
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
        return false;
    }

    /**
     * Deletes data from the adapter
     *
     * @param string $key
     *
     * @return bool
     */
    protected function doDelete(string $key): bool
    {
        if ($key === $this->fetching) {
            return false;
        }

        $exists = isset($this->weakList[$key]);
        unset($this->weakList[$key]);

        return $exists;
    }

    /**
     * Reads data from the adapter
     *
     * @param string     $key
     * @param mixed|null $defaultValue
     *
     * @return mixed
     */
    protected function doGet(string $key, mixed $defaultValue = null): mixed
    {
        /**
         * while getting a key, garbage collection might be triggered,
         * this will stop unsetting the key, will not stop however the model
         * gets destroyed by GC,
         * this is for the destruct that is in the model
         * not do destroy the key before getting it.
         */
        $this->fetching = $key;
        if (false === isset($this->weakList[$key])) {
            $this->fetching = null;

            return $defaultValue;
        }

        $reference      = $this->weakList[$key];
        $value          = $reference->get();
        $this->fetching = null;
        /**
         * value could be null, object could be destroyed while fetching
         */
        if (null === $value) {
            $this->delete($key);
        }

        return $value;
    }

    /**
     * Checks if an element exists in the cache
     *
     * @param string $key
     *
     * @return bool
     */
    protected function doHas(string $key): bool
    {
        return isset($this->weakList[$key]);
    }

    /**
     * Increments a stored number
     *
     * @param string $key
     * @param int    $value
     *
     * @return false|int
     */
    protected function doIncrement(string $key, int $value = 1): false | int
    {
        return false;
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
     * @throws BaseException
     */
    protected function doSet(string $key, mixed $value, mixed $ttl = null): bool
    {
        if (is_int($ttl) && $ttl < 1) {
            return $this->delete($key);
        }

        if (!is_object($value)) {
            return false;
        }

        if (false === isset($this->weakList[$key])) {
            $this->weakList[$key] = WeakReference::create($value);
        }

        return true;
    }
}
