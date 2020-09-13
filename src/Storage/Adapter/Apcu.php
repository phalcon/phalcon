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

use APCuIterator;
use DateInterval;
use Exception;
use Phalcon\Helper\Exception as ExceptionAlias;
use Phalcon\Storage\SerializerFactory;
use function apcu_dec;
use function apcu_delete;
use function apcu_exists;
use function apcu_fetch;
use function apcu_inc;
use function apcu_store;
use function is_object;

/**
 * Apcu adapter
 *
 * @property array $options
 */
class Apcu extends AbstractAdapter
{
    /**
     * @var string
     */
    protected string $prefix = 'ph-apcu-';

    /**
     * Apcu constructor.
     *
     * @param SerializerFactory $factory
     * @param array             $options
     *
     * @throws ExceptionAlias
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
        $pattern = '/^' . $this->prefix . '/';
        $apc     = new APCuIterator($pattern);
        $result  = true;

        if (!is_object($apc)) {
            return false;
        }

        foreach ($apc as $item) {
            if (!apcu_delete($item['key'])) {
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
     * @return bool|int
     */
    public function decrement(string $key, int $value = 1)
    {
        return apcu_dec($this->getPrefixedKey($key), $value);
    }

    /**
     * Reads data from the adapter
     *
     * @param string $key
     *
     * @return bool
     */
    public function delete(string $key): bool
    {
        return (bool) apcu_delete($this->getPrefixedKey($key));
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
        $content = apcu_fetch($this->getPrefixedKey($key));

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
        $pattern = '/^' . $this->prefix . $prefix . '/';
        $apc     = new APCuIterator($pattern);
        $results = [];

        if (!is_object($apc)) {
            return $results;
        }

        foreach ($apc as $item) {
            $results[] = $item['key'];
        }

        return $results;
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
        return apcu_exists($this->getPrefixedKey($key));
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
        return apcu_inc($this->getPrefixedKey($key), $value);
    }

    /**
     * Stores data in the adapter
     *
     * @param string                $key
     * @param mixed                 $value
     * @param DateInterval|int|null $ttl
     *
     * @return bool
     * @throws Exception
     */
    public function set(string $key, $value, $ttl = null): bool
    {
        return apcu_store(
            $this->getPrefixedKey($key),
            $this->getSerializedData($value),
            $this->getTtl($ttl)
        );
    }
}
