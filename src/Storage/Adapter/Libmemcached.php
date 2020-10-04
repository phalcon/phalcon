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
use Phalcon\Support\HelperFactory;

use function array_merge;
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
     * @param HelperFactory     $helperFactory
     * @param SerializerFactory $factory
     * @param array             $options
     *
     * @throws SupportException
     */
    public function __construct(
        HelperFactory $helperFactory,
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

        parent::__construct($helperFactory, $factory, $options);
    }

    /**
     * Flushes/clears the cache
     *
     * @return bool
     * @throws StorageException
     */
    public function clear(): bool
    {
        return $this->getAdapter()->flush();
    }

    /**
     * Decrements a stored number
     *
     * @param string $key
     * @param int    $value
     *
     * @return bool|int
     * @throws StorageException
     */
    public function decrement(string $key, int $value = 1)
    {
        return $this->getAdapter()->decrement($key, $value);
    }

    /**
     * Reads data from the adapter
     *
     * @param string $key
     *
     * @return bool
     * @throws StorageException
     */
    public function delete(string $key): bool
    {
        return $this->getAdapter()->delete($key, 0);
    }

    /**
     * Reads data from the adapter
     *
     * @param string     $key
     * @param mixed|null $defaultValue
     *
     * @return mixed|null
     * @throws StorageException
     */
    public function get(string $key, $defaultValue = null)
    {
        return $this->getUnserializedData(
            $this->getAdapter()->get($key),
            $defaultValue
        );
    }

    /**
     * Returns the already connected adapter or connects to the Memcached
     * server(s)
     *
     * @return Memcached|mixed
     * @throws StorageException
     */
    public function getAdapter()
    {
        if (null === $this->adapter) {
            $persistentId = $this->helperFactory->get(
                $this->options,
                'persistentId',
                'ph-mcid-'
            );
            /** @var array $sasl */
            $sasl       = $this->helperFactory->get(
                $this->options,
                'saslAuthData',
                []
            );
            $connection = new Memcached($persistentId);
            $serverList = $connection->getServerList();

            $connection->setOption(Memcached::OPT_PREFIX_KEY, $this->prefix);

            if (count($serverList) < 1) {
                /** @var array $servers */
                $servers  = $this->helperFactory->get(
                    $this->options,
                    'servers',
                    []
                );
                /** @var array $client */
                $client   = $this->helperFactory->get(
                    $this->options,
                    'client',
                    []
                );
                /** @var string $saslUser */
                $saslUser = $this->helperFactory->get($sasl, 'user', '');
                /** @var string $saslPass */
                $saslPass = $this->helperFactory->get($sasl, 'pass', '');
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
            $this->getAdapter()->getAllKeys(),
            $prefix
        );
    }

    /**
     * Checks if an element exists in the cache
     *
     * @param string $key
     *
     * @return bool
     * @throws StorageException
     */
    public function has(string $key): bool
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
     * @return bool|int
     * @throws StorageException
     */
    public function increment(string $key, int $value = 1)
    {
        return $this->getAdapter()->increment($key, $value);
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
     * @throws StorageException
     */
    public function set(string $key, $value, $ttl = null): bool
    {
        return $this->getAdapter()->set(
            $key,
            $this->getSerializedData($value),
            $this->getTtl($ttl)
        )
            ;
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
     */
    private function setSerializer(Memcached $connection)
    {
        $map = [
            'php'      => Memcached::SERIALIZER_PHP,
            'json'     => Memcached::SERIALIZER_JSON,
            'igbinary' => Memcached::SERIALIZER_IGBINARY,
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
