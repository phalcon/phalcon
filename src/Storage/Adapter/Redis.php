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
use Phalcon\Helper\Exception as ExceptionAlias;
use Phalcon\Storage\Exception;
use Phalcon\Storage\SerializerFactory;
use Redis as RedisService;

use function constant;
use function defined;
use function mb_strtolower;

/**
 * Redis adapter
 *
 * @property array $options
 */
class Redis extends AbstractAdapter
{
    /**
     * @var string
     */
    protected string $prefix = 'ph-reds-';

    /**
     * Redis constructor.
     *
     * @param SerializerFactory $factory
     * @param array             $options
     */
    public function __construct(SerializerFactory $factory, array $options = [])
    {
        /**
         * Lets set some defaults and options here
         */
        $options['host']       = $options['host'] ?? '127.0.0.1';
        $options['port']       = (int) $options['port'] ?? 6379;
        $options['index']      = $options['index'] ?? 0;
        $options['persistent'] = $options['persistent'] ?? false;
        $options['auth']       = $options['auth'] ?? '';
        $options['socket']     = $options['socket'] ?? '';

        parent::__construct($factory, $options);
    }

    /**
     * Flushes/clears the cache
     *
     * @return bool
     * @throws Exception
     */
    public function clear(): bool
    {
        return $this->getAdapter()->flushDB();
    }

    /**
     * Decrements a stored number
     *
     * @param string $key
     * @param int    $value
     *
     * @return bool|false|int
     * @throws Exception
     */
    public function decrement(string $key, int $value = 1)
    {
        return $this->getAdapter()->decrBy($key, $value);
    }

    /**
     * Reads data from the adapter
     *
     * @param string $key
     *
     * @return bool
     * @throws Exception
     */
    public function delete(string $key): bool
    {
        return (bool) $this->getAdapter()->del($key);
    }

    /**
     * Reads data from the adapter
     *
     * @param string $key
     * @param null   $defaultValue
     *
     * @return mixed
     * @throws Exception
     */
    public function get(string $key, $defaultValue = null)
    {
        return $this->getUnserializedData(
            $this->getAdapter()->get($key),
            $defaultValue
        );
    }

    /**
     * Returns the already connected adapter or connects to the Redis
     * server(s)
     *
     * @return mixed|RedisService
     * @throws Exception
     */
    public function getAdapter()
    {
        if (null === $this->adapter) {
            $connection = new RedisService();
            $auth       = $this->options['auth'];
            $host       = $this->options['host'];
            $port       = $this->options['port'];
            $index      = $this->options['index'];
            $persistent = $this->options['persistent'];

            if (!$persistent) {
                $result = $connection->connect($host, $port, $this->lifetime);
            } else {
                $persistentId = 'persistentid_' . $index;
                $result       = $connection->pconnect(
                    $host,
                    $port,
                    $this->lifetime,
                    $persistentId
                );
            }

            $this
                ->checkConnect($result, $host, $port)
                ->checkAuth($auth, $connection)
                ->checkIndex($index, $connection)
            ;

            $connection->setOption(RedisService::OPT_PREFIX, $this->prefix);

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
     */
    public function getKeys(string $prefix = ''): array
    {
        return $this->getFilteredKeys(
            $this->getAdapter()->keys('*'),
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
     */
    public function has(string $key): bool
    {
        return (bool) $this->getAdapter()->exists($key);
    }

    /**
     * Increments a stored number
     *
     * @param string $key
     * @param int    $value
     *
     * @return bool|false|int
     * @throws Exception
     */
    public function increment(string $key, int $value = 1)
    {
        return $this->getAdapter()->incrBy($key, $value);
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
     * @param string       $auth
     * @param RedisService $connection
     *
     * @return Redis
     * @throws Exception
     */
    private function checkAuth($auth, RedisService $connection): Redis
    {
        if (!empty($auth) && !$connection->auth($auth)) {
            throw new Exception(
                'Failed to authenticate with the Redis server'
            );
        }

        return $this;
    }

    /**
     * @param bool   $result
     * @param string $host
     * @param int    $port
     *
     * @return Redis
     * @throws Exception
     */
    private function checkConnect(bool $result, string $host, int $port): Redis
    {
        if (!$result) {
            throw new Exception(
                'Could not connect to the Redisd server [' . $host . ':' . $port . ']'
            );
        }

        return $this;
    }

    /**
     * @param int          $index
     * @param RedisService $connection
     *
     * @return Redis
     * @throws Exception
     */
    private function checkIndex(int $index, RedisService $connection): Redis
    {
        if ($index > 0 && !$connection->select($index)) {
            throw new Exception(
                'Redis server selected database failed'
            );
        }

        return $this;
    }

    /**
     * Checks the serializer. If it is a supported one it is set, otherwise
     * the custom one is set.
     *
     * @param RedisService $connection
     *
     * @throws ExceptionAlias
     */
    private function setSerializer(RedisService $connection)
    {
        $map = [
            'none' => RedisService::SERIALIZER_NONE,
            'php'  => RedisService::SERIALIZER_PHP,
        ];

        /**
         * In case IGBINARY or MSGPACK are not defined for previous versions
         * of Redis
         */
        if (defined('\\Redis::SERIALIZER_IGBINARY')) {
            $map['igbinary'] = constant('\\Redis::SERIALIZER_IGBINARY');
        }

        if (defined('\\Redis::SERIALIZER_MSGPACK')) {
            $map['msgpack'] = constant('\\Redis::SERIALIZER_MSGPACK');
        }

        $serializer = mb_strtolower($this->defaultSerializer);

        if (isset($map[$serializer])) {
            $this->defaultSerializer = '';
            $connection->setOption(RedisService::OPT_SERIALIZER, $map[$serializer]);
        } else {
            $this->initSerializer();
        }
    }
}
