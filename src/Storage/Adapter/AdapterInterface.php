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

/**
 * Interface for Phalcon\Logger adapters
 */
interface AdapterInterface
{
    /**
     * Flushes/clears the cache
     */
    public function clear(): bool;

    /**
     * Decrements a stored number
     *
     * @param string $key
     * @param int    $value
     *
     * @return false|int
     */
    public function decrement(string $key, int $value = 1): false | int;

    /**
     * Deletes data from the adapter
     *
     * @param string $key
     *
     * @return bool
     */
    public function delete(string $key): bool;

    /**
     * Reads data from the adapter
     *
     * @param string     $key
     * @param mixed|null $defaultValue
     *
     * @return mixed
     */
    public function get(string $key, mixed $defaultValue = null): mixed;

    /**
     * Returns the adapter - connects to the storage if not connected
     *
     * @return mixed
     */
    public function getAdapter(): mixed;

    /**
     * Returns all the keys stored
     *
     * @param string $prefix
     *
     * @return array
     */
    public function getKeys(string $prefix = ''): array;

    /**
     * Returns the prefix for the keys
     *
     * @return string
     */
    public function getPrefix(): string;

    /**
     * Checks if an element exists in the cache
     *
     * @param string $key
     *
     * @return bool
     */
    public function has(string $key): bool;

    /**
     * Increments a stored number
     *
     * @param string $key
     * @param int    $value
     *
     * @return false|int
     */
    public function increment(string $key, int $value = 1): false | int;

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
    public function set(string $key, mixed $value, mixed $ttl = null): bool;

    /**
     * Stores data in the adapter forever. The key needs to be manually deleted
     * from the adapter.
     *
     * @param string $key
     * @param mixed  $data
     *
     * @return bool
     */
    public function setForever(string $key, mixed $data): bool;
}
