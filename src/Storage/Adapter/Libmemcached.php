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
use Memcached;
use Phalcon\Storage\Exception as StorageException;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Support\Exception as SupportException;

use function array_merge;
use function is_bool;
use function is_int;
use function strtolower;

/**
 * Libmemcached adapter
 */
class Libmemcached extends AbstractAdapter
{
    /**
     * @var string
     */
    protected string $prefix = 'ph-memc-';

    /**
     * Libmemcached constructor.
     *
     * @param SerializerFactory $factory
     * @param array             $options
     */
    public function __construct(
        SerializerFactory $factory,
        array $options = []
    ) {
        if (true !== isset($options['servers'])) {
            $options['servers'] = [
                0 => [
                    'host'   => '127.0.0.1',
                    'port'   => 11211,
                    'weight' => 1,
                ],
            ];
        }

        parent::__construct($factory, $options);
    }

    /**
     * Flushes/clears the cache
     *
     * @return bool
     * @throws StorageException
     */
    public function clear(): bool
    {
        return $this->getAdapter()
                    ->flush()
        ;
    }

    /**
     * Returns the already connected adapter or connects to the Memcached
     * server(s)
     *
     * @return Memcached|mixed
     * @throws StorageException
     */
    public function getAdapter(): mixed
    {
        if (null === $this->adapter) {
            $persistentId = $this->options['persistentId'] ?? 'ph-mcid-';
            /** @var array $sasl */
            $sasl       = $this->options['saslAuthData'] ?? [];
            $connection = new Memcached($persistentId);
            $serverList = $connection->getServerList();

            $connection->setOption(Memcached::OPT_PREFIX_KEY, $this->prefix);

            if (count($serverList) < 1) {
                /** @var array $servers */
                $servers = $this->options['servers'] ?? [];
                /** @var array $client */
                $client = $this->options['client'] ?? [];
                /** @var string $saslUser */
                $saslUser = $sasl['user'] ?? '';
                /** @var string $saslPass */
                $saslPass = $sasl['pass'] ?? '';
                $failover = [
                    Memcached::OPT_CONNECT_TIMEOUT       => 10,
                    Memcached::OPT_DISTRIBUTION          => Memcached::DISTRIBUTION_CONSISTENT,
                    Memcached::OPT_SERVER_FAILURE_LIMIT  => 2,
                    Memcached::OPT_REMOVE_FAILED_SERVERS => true,
                    Memcached::OPT_RETRY_TIMEOUT         => 1,
                ];
                $client   = array_merge($failover, $client);

                $this
                    ->setOptions($connection, $client)
                    ->setServers($connection, $servers)
                    ->setSasl($connection, $saslUser, $saslPass)
                ;
            }

            $this->setSerializer($connection);

            $this->adapter = $connection;
        }

        return $this->adapter;
    }

    /**
     * Stores data in the adapter
     *
     * @param string $prefix
     *
     * @return array
     * @throws StorageException
     */
    public function getKeys(string $prefix = ''): array
    {
        return $this->getFilteredKeys(
            $this->getAdapter()
                 ->getAllKeys(),
            $prefix
        );
    }

    /**
     * Stores data in the adapter forever. The key needs to be manually deleted
     * from the adapter.
     *
     * @param string $key
     * @param mixed  $data
     *
     * @return bool
     * @throws StorageException
     */
    public function setForever(string $key, mixed $data): bool
    {
        $result = $this->getAdapter()
                       ->set($key, $this->getSerializedData($data), 0)
        ;

        return is_bool($result) ? $result : false;
    }

    /**
     * Decrements a stored number
     *
     * @param string $key
     * @param int    $value
     *
     * @return false|int
     * @throws StorageException
     */
    protected function doDecrement(string $key, int $value = 1): false | int
    {
        return $this->getAdapter()
                    ->decrement($key, $value)
        ;
    }

    /**
     * Reads data from the adapter
     *
     * @param string $key
     *
     * @return bool
     * @throws StorageException
     */
    protected function doDelete(string $key): bool
    {
        return $this->getAdapter()
                    ->delete($key, 0)
        ;
    }

    /**
     * Checks if an element exists in the cache
     *
     * @param string $key
     *
     * @return bool
     * @throws StorageException
     */
    protected function doHas(string $key): bool
    {
        $connection = $this->getAdapter();
        $connection->get($key);

        return Memcached::RES_NOTFOUND !== $connection->getResultCode();
    }

    /**
     * Increments a stored number
     *
     * @param string $key
     * @param int    $value
     *
     * @return false|int
     * @throws StorageException
     */
    protected function doIncrement(string $key, int $value = 1): false | int
    {
        return $this->getAdapter()->increment($key, $value);
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
     * @throws StorageException
     */
    protected function doSet(string $key, mixed $value, mixed $ttl = null): bool
    {
        if (true === is_int($ttl) && $ttl < 1) {
            return $this->delete($key);
        }

        $result = $this->getAdapter()
                       ->set(
                           $key,
                           $this->getSerializedData($value),
                           $this->getTtl($ttl)
                       )
        ;

        return is_bool($result) ? $result : false;
    }

    /**
     * @param Memcached $connection
     * @param array     $client
     *
     * @return Libmemcached
     * @throws StorageException
     */
    private function setOptions(Memcached $connection, array $client): Libmemcached
    {
        if (true !== $connection->setOptions($client)) {
            throw new StorageException(
                'Cannot set Memcached client options'
            );
        }

        return $this;
    }

    /**
     * @param Memcached $connection
     * @param string    $saslUser
     * @param string    $saslPass
     *
     * @return Libmemcached
     */
    private function setSasl(Memcached $connection, string $saslUser, string $saslPass): Libmemcached
    {
        if (true !== empty($saslUser)) {
            $connection->setSaslAuthData($saslUser, $saslPass);
        }

        return $this;
    }

    /**
     * Checks the serializer. If it is a supported one it is set, otherwise
     * the custom one is set.
     *
     * @param Memcached $connection
     *
     * @return void
     * @throws SupportException
     */
    private function setSerializer(Memcached $connection): void
    {
        $map = [
            'memcached_php'      => Memcached::SERIALIZER_PHP,
            'memcached_json'     => Memcached::SERIALIZER_JSON,
            'memcached_igbinary' => Memcached::SERIALIZER_IGBINARY,
        ];

        $serializer = strtolower($this->defaultSerializer);

        if (isset($map[$serializer])) {
            $this->defaultSerializer = '';
            $connection->setOption(Memcached::OPT_SERIALIZER, $map[$serializer]);
        }

        $this->initSerializer();
    }

    /**
     * @param Memcached $connection
     * @param array     $servers
     *
     * @return Libmemcached
     * @throws StorageException
     */
    private function setServers(Memcached $connection, array $servers): Libmemcached
    {
        if (true !== $connection->addServers($servers)) {
            throw new StorageException(
                'Cannot connect to the Memcached server(s)'
            );
        }

        return $this;
    }
}
