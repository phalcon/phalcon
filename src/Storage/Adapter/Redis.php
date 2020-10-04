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
use Phalcon\Storage\Exception as StorageException;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Support\Exception as SupportException;
use Phalcon\Support\HelperFactory;
use Redis as RedisService;

use function call_user_func_array;
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
        /**
         * Lets set some defaults and options here
         */
        $options['host']       = $helperFactory->get($options, 'host', '127.0.0.1');
        $options['port']       = $helperFactory->get($options, 'port', 6379, 'int');
        $options['index']      = $helperFactory->get($options, 'index', 0);
        $options['persistent'] = $helperFactory->get($options, 'persistent', false);
        $options['auth']       = $helperFactory->get($options, 'auth', '');
        $options['socket']     = $helperFactory->get($options, 'socket', '');

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
        return $this->getAdapter()->flushDB();
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
        return $this->getAdapter()->decrBy($key, $value);
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
        return (bool) $this->getAdapter()->unlink($key);
    }

    /**
     * Reads data from the adapter
     *
     * @param string $key
     * @param null   $defaultValue
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
     * Returns the already connected adapter or connects to the Redis
     * server(s)
     *
     * @return mixed|RedisService
     * @throws StorageException
     */
    public function getAdapter()
    {
        if (null === $this->adapter) {
            $connection = new RedisService();

            $this
                ->checkConnect($connection)
                ->checkAuth($connection)
                ->checkIndex($connection)
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
     * @throws StorageException
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
     * @throws StorageException
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
     * @throws StorageException
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
     * @throws BaseException
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
     * @param RedisService $connection
     *
     * @return Redis
     * @throws StorageException
     */
    private function checkAuth(RedisService $connection): Redis
    {
        $auth  = $this->options['auth'];

        try {
            $error = (true !== empty($auth) && true !== $connection->auth($auth));
        } catch (BaseException $ex) {
            $error = true;
        }

        if (true === $error) {
            throw new StorageException(
                'Failed to authenticate with the Redis server'
            );
        }

        return $this;
    }

    /**
     * @param RedisService $connection
     *
     * @return Redis
     * @throws StorageException
     */
    private function checkConnect(RedisService $connection): Redis
    {
        $persistent   = $this->options['persistent'];
        $method       = $persistent ? 'connect' : 'pconnect';
        $options      = [
            $this->options['host'],
            $this->options['port'],
            $this->lifetime
        ];

        if (true === $persistent) {
            $options[] = 'persistentid_' . $this->options['index'];
        }

        $result = call_user_func_array([$connection, $method], $options);

        if (true !== $result) {
            throw new StorageException(
                'Could not connect to the Redisd server [' .
                $this->options['host'] .
                ':' .
                $this->options['port'] .
                ']'
            );
        }

        return $this;
    }

    /**
     * @param RedisService $connection
     *
     * @return Redis
     * @throws StorageException
     */
    private function checkIndex(RedisService $connection): Redis
    {
        $index = $this->options['index'];

        if ($index > 0 && true !== $connection->select($index)) {
            throw new StorageException(
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

        if (true === isset($map[$serializer])) {
            $this->defaultSerializer = '';
            $connection->setOption(RedisService::OPT_SERIALIZER, $map[$serializer]);
        }

        $this->initSerializer();
    }
}
