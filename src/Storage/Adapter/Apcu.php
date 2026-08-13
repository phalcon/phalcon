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
use Exception;
use Phalcon\Contracts\Storage\StorageTypes;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Traits\Php\ApcuTrait;

use function is_bool;
use function is_int;

/**
 * Apcu adapter
 *
 * Capabilities:
 * - Counters: native atomic (apcu_inc()/apcu_dec()).
 * - getKeys(): APCUIterator regex scan over the shared APCu store.
 * - Serializers: Phalcon-side only; no backend-native serializer.
 *
 * @phpstan-import-type storage_adapter_options from StorageTypes
 * @phpstan-import-type storage_keys from StorageTypes
 *
 * @phpstan-property storage_adapter_options $options
 */
class Apcu extends AbstractAdapter
{
    use ApcuTrait;

    protected string $prefix = 'ph-apcu-';

    /**
     * Apcu constructor.
     *
     * @phpstan-param storage_adapter_options $options
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
     * @phpstan-return storage_keys
     */
    public function getKeys(string $prefix = ''): array
    {
        $pattern = '/^' . $this->prefix . $prefix . '/';
        $apc     = $this->phpApcuIterator($pattern);
        $results = [];

        if (!is_object($apc)) {
            return $results;
        }

        /** @var array{key: string} $item */
        foreach ($apc as $item) {
            $results[] = $item['key'];
        }

        return $results;
    }

    /**
     * Stores data in the adapter forever. The key needs to manually deleted
     * from the adapter.
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
     */
    protected function doDecrement(string $key, int $value = 1): false | int
    {
        $result = $this->phpApcuDec($this->getPrefixedKey($key), $value);

        return is_int($result) ? $result : false;
    }

    /**
     * Deletes data from the adapter
     */
    protected function doDelete(string $key): bool
    {
        return (bool)$this->phpApcuDelete($this->getPrefixedKey($key));
    }

    /**
     * Deletes multiple keys from APCu in a single call
     *
     * @phpstan-param storage_keys $keys
     */
    protected function doDeleteMultiple(array $keys): bool
    {
        $prefixedKeys = array_map(fn($key) => $this->getPrefixedKey($key), $keys);
        $result       = $this->phpApcuDelete($prefixedKeys);

        // apcu_delete with array returns array of keys that could NOT be deleted
        return is_array($result) && empty($result);
    }

    protected function doGetData(string $key): mixed
    {
        return $this->phpApcuFetch($this->getPrefixedKey($key));
    }

    /**
     * Checks if an element exists in the cache
     */
    protected function doHas(string $key): bool
    {
        $result = $this->phpApcuExists($this->getPrefixedKey($key));

        return is_bool($result) ? $result : false;
    }

    /**
     * Increments a stored number
     */
    protected function doIncrement(string $key, int $value = 1): false | int
    {
        $result = $this->phpApcuInc($this->getPrefixedKey($key), $value);

        return is_int($result) ? $result : false;
    }

    /**
     * Stores data in the adapter. If the TTL is `null` (default) or not defined
     * then the default TTL will be used, as set in this adapter. If the TTL
     * is `0` or a negative number, a `delete()` will be issued, since this
     * item has expired. If you need to set this key forever, you should use
     * the `setForever()` method.
     *
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
