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

namespace Phalcon\Tests;

use Exception;
use Memcached;
use Predis\Client as RedisDriver;

use function array_slice;
use function call_user_func_array;
use function func_get_args;
use function getOptionsLibmemcached;
use function getOptionsRedis;

abstract class AbstractServicesTestCase extends AbstractUnitTestCase
{
    public function clearMemcached(): void
    {
        $adapter = $this->getMemcached();

        $adapter->flush();
    }

    /**
     * Checks whether a key exists
     *
     * @param string $key The key name
     */
    public function doesNotHaveMemcachedKey(string $key): bool
    {
        return false === $this->hasMemcachedKey($key);
    }

    /**
     * Checks whether a key exists
     *
     * @param string $key The key name
     */
    public function doesNotHaveRedisKey(string $key): bool
    {
        return false === $this->hasRedisKey($key);
    }

    /**
     * Returns the value of a given key
     *
     * @param string     $key
     * @param mixed|null $value
     *
     * @return void
     */
    public function getMemcachedKey(string $key): mixed
    {
        $adapter = $this->getMemcached();

        return $adapter->get($key);
    }

    /**
     * Returns the value of a given key
     *
     * @param string $key The key name
     *
     * @throws Exception if the key does not exist
     */
    public function getRedisKey(string $key): array | string | null
    {
        $adapter = $this->getRedis();

        return $adapter->get($key);
    }

    /**
     * Checks whether a key exists
     *
     * @param string $key The key name
     */
    public function hasMemcachedKey(string $key): bool
    {
        $adapter = $this->getMemcached();

        return null !== $adapter->get($key);
    }

    /**
     * Checks whether a key exists
     *
     * @param string $key The key name
     */
    public function hasRedisKey(string $key): bool
    {
        $adapter = $this->getRedis();

        return (bool)$adapter->exists($key);
    }

    /**
     * Sends a command directly to the Redis driver. See documentation at
     * https://github.com/nrk/predis
     * Every argument that follows the $command name will be passed to it.
     *
     * @param string $command The command name
     *
     * @return mixed
     */
    public function sendRedisCommand(string $command): mixed
    {
        return call_user_func_array(
            [$this->getRedis(), $command],
            array_slice(func_get_args(), 1)
        );
    }

    /**
     * Creates or modifies keys
     *
     * @param string $key
     * @param mixed  $value
     * @param int    $expiration
     *
     * @return void
     */
    public function setMemcachedKey(
        string $key,
        mixed $value,
        int $expiration = 0
    ): void {
        $adapter = $this->getMemcached();

        $this->assertTrue($adapter->set($key, $value, $expiration));
    }

    /**
     * Creates or modifies keys
     *
     * @param string $key   The key name
     * @param mixed  $value The value
     *
     * @throws Exception
     */
    public function setRedisKey(
        string $key,
        mixed $value
    ): void {
        $adapter = $this->getRedis();

        $result = $adapter->set($key, $value);

        if (false === $result) {
            throw new Exception('Cannot set key in Redis');
        }
    }

    /**
     * @return Memcached
     */
    private function getMemcached(): Memcached
    {
        $options = getOptionsLibmemcached();
        $server  = $options['servers'][0];
        $adapter = new Memcached();
        $adapter->addServer($server['host'], (int)$server['port']);

        return $adapter;
    }

    /**
     * @return RedisDriver
     */
    private function getRedis(): RedisDriver
    {
        try {
            $adapter = new RedisDriver(getOptionsRedis());

            return $adapter;
        } catch (Exception $exception) {
            $this->markTestSkipped(
                __CLASS__ . ' - ' . $exception->getMessage()
            );
        }
    }
}
