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

use APCUIterator;
use DateInterval;
use Exception;
use Phalcon\Storage\SerializerFactory;
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
     * @throws Exception
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
        $result = true;
        $apc    = $this->getKeys();

        foreach ($apc as $item) {
            if (true !== $this->phpApcuDelete($item)) {
                $result = false;
            }
        }

        return $result;
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

        foreach ($apc as $item) {
            $results[] = $item['key'];
        }

        return $results;
    }

    /**
     * Stores data in the adapter forever. The key needs to manually deleted
     * from the adapter.
     *
     * @param string $key
     * @param mixed  $data
     *
     * @return bool
     */
    public function setForever(string $key, mixed $data): bool
    {
        $result = $this->phpApcuStore(
            $this->getPrefixedKey($key),
            $this->getSerializedData($data)
        );

        return is_bool($result) ? $result : false;
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
        return $this->phpApcuDec($this->getPrefixedKey($key), $value);
    }

    /**
     * Reads data from the adapter
     *
     * @param string $key
     *
     * @return bool
     */
    protected function doDelete(string $key): bool
    {
        return (bool)$this->phpApcuDelete($this->getPrefixedKey($key));
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    protected function doGetData(string $key): mixed
    {
        return $this->phpApcuFetch($this->getPrefixedKey($key));
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
        $result = $this->phpApcuExists($this->getPrefixedKey($key));

        return is_bool($result) ? $result : false;
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
    protected function doSet(string $key, mixed $value, mixed $ttl = null): bool
    {
        if (is_int($ttl) && $ttl < 1) {
            return $this->delete($key);
        }

        $result = $this->phpApcuStore(
            $this->getPrefixedKey($key),
            $this->getSerializedData($value),
            $this->getTtl($ttl)
        );

        return is_bool($result) ? $result : false;
    }
}
