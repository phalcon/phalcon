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
use Exception;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Support\Exception as SupportException;
use Phalcon\Support\Traits\PhpApcuTrait;

use function is_bool;
use function is_int;

/**
 * Apcu adapter
 *
 * @property array $options
 */
class Apcu extends AbstractAdapter
{
    use PhpApcuTrait;

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
     * @throws SupportException
     */
    public function __construct(
        SerializerFactory $factory,
        array $options = []
    ) {
        parent::__construct($factory, $options);

        $this->initSerializer();
    }

    /**
     * Flushes/clears the cache
     */
    public function clear(): bool
    {
        $pattern = '/^' . $this->prefix . '/';
        $apc     = $this->phpApcuIterator($pattern);
        $result  = true;

        if (true !== is_object($apc)) {
            return false;
        }

        foreach ($apc as $item) {
            if (true !== $this->phpApcuDelete($item['key'])) {
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
        return $this->phpApcuDec($this->getPrefixedKey($key), $value);
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
        return (bool) $this->phpApcuDelete($this->getPrefixedKey($key));
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
        $apc     = $this->phpApcuIterator($pattern);
        $results = [];

        if (true !== is_object($apc)) {
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
        $result = $this->phpApcuExists($this->getPrefixedKey($key));

        return is_bool($result) ? $result : false;
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
        return $this->phpApcuInc($this->getPrefixedKey($key), $value);
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
     * @throws Exception
     */
    public function set(string $key, $value, $ttl = null): bool
    {
        if (true === is_int($ttl) && $ttl < 1) {
            return $this->delete($key);
        }

        $result = $this->phpApcuStore(
            $this->getPrefixedKey($key),
            $this->getSerializedData($value),
            $this->getTtl($ttl)
        );

        return is_bool($result) ? $result : false;
    }

    /**
     * Stores data in the adapter forever. The key needs to manually deleted
     * from the adapter.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return bool
     */
    public function setForever(string $key, $value): bool
    {
        $result = $this->phpApcuStore(
            $this->getPrefixedKey($key),
            $this->getSerializedData($value)
        );

        return is_bool($result) ? $result : false;
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    protected function doGet(string $key)
    {
        return $this->phpApcuFetch($this->getPrefixedKey($key));
    }
}
