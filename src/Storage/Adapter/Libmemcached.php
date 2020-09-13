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
use Memcached;
use Phalcon\Helper\Exception as ExceptionAlias;
use Phalcon\Storage\Exception;
use Phalcon\Storage\SerializerFactory;

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
     * @param SerializerFactory $factory
     * @param array             $options
     */
    public function __construct(SerializerFactory $factory, array $options = [])
    {
        if (!isset($options['servers'])) {
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
     * @throws Exception
     * @throws ExceptionAlias
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
     * @throws Exception
     * @throws ExceptionAlias
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
     * @throws Exception
     * @throws ExceptionAlias
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
     * @throws Exception
     * @throws ExceptionAlias
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
     * @throws Exception
     * @throws ExceptionAlias
     */
    public function getAdapter()
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
                $servers  = $this->options['servers'] ?? [];
                /** @var array $client */
                $client   = $this->options['client'] ?? [];
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
     * @throws Exception
     * @throws ExceptionAlias
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
     * @throws Exception
     * @throws ExceptionAlias
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
     * @throws Exception
     * @throws ExceptionAlias
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
     * @throws \Exception
     * @throws Exception
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
     * @throws Exception
     */
    private function setOptions(Memcached $connection, array $client): Libmemcached
    {
        if (!$connection->setOptions($client)) {
            throw new Exception(
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
        if (!empty($saslUser)) {
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
     * @throws ExceptionAlias
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
        } else {
            $this->initSerializer();
        }
    }

    /**
     * @param Memcached $connection
     * @param array     $servers
     *
     * @return Libmemcached
     * @throws Exception
     */
    private function setServers(Memcached $connection, array $servers): Libmemcached
    {
        if (!$connection->addServers($servers)) {
            throw new Exception(
                'Cannot connect to the Memcached server(s)'
            );
        }

        return $this;
    }
}
