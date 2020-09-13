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
use Phalcon\Helper\Exception as HelperException;
use Phalcon\Storage\SerializerFactory;

use function array_keys;

/**
 * Memory adapter
 *
 * @property array $data
 * @property array $options
 */
class Memory extends AbstractAdapter
{
    /**
     * @var array
     */
    protected array $data = [];

    /**
     * Memory constructor.
     *
     * @param SerializerFactory $factory
     * @param array             $options
     *
     * @throws HelperException
     */
    public function __construct(SerializerFactory $factory, array $options = [])
    {
        parent::__construct($factory, $options);

        $this->initSerializer();
    }

    /**
     * Flushes/clears the cache
     */
    public function clear(): bool
    {
        $this->data = [];

        return true;
    }

    /**
     * Decrements a stored number
     *
     * @param string $key
     * @param int    $value
     *
     * @return bool|int
     */
    public function decrement(string $key, int $value = 1)
    {
        $prefixedKey = $this->getPrefixedKey($key);
        $result      = isset($this->data[$prefixedKey]);

        if ($result) {
            $current  = $this->data[$prefixedKey];
            $newValue = (int) $current - $value;
            $result   = $newValue;

            $this->data[$prefixedKey] = $newValue;
        }

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
        $prefixedKey = $this->getPrefixedKey($key);
        $exists      = isset($this->data[$prefixedKey]);

        unset($this->data[$prefixedKey]);

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
    public function get(string $key, $defaultValue = null)
    {
        $prefixedKey = $this->getPrefixedKey($key);
        $content     = $this->data[$prefixedKey];

        return $this->getUnserializedData($content, $defaultValue);
    }

    /**
     * Stores data in the adapter
     *
     * @param string $prefix
     *
     * @return array
     */
    public function getKeys(string $prefix = ''): array
    {
        return $this->getFilteredKeys(array_keys($this->data), $prefix);
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
        $prefixedKey = $this->getPrefixedKey($key);

        return isset($this->data[$prefixedKey]);
    }

    /**
     * Increments a stored number
     *
     * @param string $key
     * @param int    $value
     *
     * @return bool|int
     */
    public function increment(string $key, int $value = 1)
    {
        $prefixedKey = $this->getPrefixedKey($key);
        $result      = isset($this->data[$prefixedKey]);

        if ($result) {
            $current  = $this->data[$prefixedKey];
            $newValue = (int) $current + $value;
            $result   = $newValue;

            $this->data[$prefixedKey] = $newValue;
        }

        return $result;
    }

    /**
     * Stores data in the adapter
     *
     * @param string                $key
     * @param mixed                 $value
     * @param DateInterval|int|null $ttl
     *
     * @return bool
     * @throws BaseException
     */
    public function set(string $key, $value, $ttl = null): bool
    {
        $content     = $this->getSerializedData($value);
        $prefixedKey = $this->getPrefixedKey($key);

        $this->data[$prefixedKey] = $content;

        return true;
    }
}
