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
use Redis as RedisService;

use function constant;
use function defined;
use function is_bool;
use function is_int;
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
     *
     * @throws SupportException
     */
    public function __construct(
        SerializerFactory $factory,
        array $options = []
    ) {
        /**
         * Lets set some defaults and options here
         */
        $options["host"]           = $options["host"] ?? "127.0.0.1";
        $options["port"]           = (int) ($options["port"] ?? 6379);
        $options["index"]          = $options["index"] ?? 0;
        $options["timeout"]        = $options["timeout"] ?? 0;
        $options["persistent"]     = (bool) ($options["persistent"] ?? false);
        $options["persistentId"]   = (string) ($options["persistentId"] ?? "");
        $options["auth"]           = $options["auth"] ?? "";
        $options["socket"]         = $options["socket"] ?? "";
        $options["connectTimeout"] = $options["connectTimeout"] ?? 0;
        $options["retryInterval"]  = $options["retryInterval"] ?? 0;
        $options["readTimeout"]    = $options["readTimeout"] ?? 0;

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
                    ->flushDB()
        ;
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
        return $this->getAdapter()
                    ->decrBy($key, $value)
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
    public function delete(string $key): bool
    {
        return (bool) $this->getAdapter()
                           ->unlink($key)
        ;
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
            $this->getAdapter()
                 ->keys('*'),
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
        return (bool) $this->getAdapter()
                           ->exists($key)
        ;
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
        return $this->getAdapter()
                    ->incrBy($key, $value)
        ;
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
     */
    public function set(string $key, $value, $ttl = null): bool
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
        $result = $this->getAdapter()
                       ->set($key, $this->getSerializedData($value))
        ;

        return is_bool($result) ? $result : false;
    }

    /**
     * @param RedisService $connection
     *
     * @return Redis
     * @throws StorageException
     */
    private function checkAuth(RedisService $connection): Redis
    {
        $auth = $this->options['auth'];

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
        $options       = $this->options;
        $host          = $options["host"];
        $port          = $options["port"];
        $timeout       = $options["timeout"];
        $retryInterval = $options["retryInterval"];
        $readTimeout   = $options["readTimeout"];

        if (true === $options["persistent"]) {
            $method    = "connect";
            $parameter = null;
        } else {
            $method       = "pconnect";
            $persistentId = $options["persistentId"];
            $parameter    = !empty($persistentId) ?: "persistentId" . $options["index"];
        }

        $result = $connection->$method(
            $host,
            $port,
            $timeout,
            $parameter,
            $retryInterval,
            $readTimeout
        );

        if (true !== $result) {
            throw new StorageException(
                sprintf(
                    "Could not connect to the Redis server [%s:%s]",
                    $host,
                    $port
                )
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
            'redis_none' => RedisService::SERIALIZER_NONE,
            'redis_php'  => RedisService::SERIALIZER_PHP,
        ];

        /**
         * In case IGBINARY or MSGPACK are not defined for previous versions
         * of Redis
         */
        if (defined('\\Redis::SERIALIZER_IGBINARY')) {
            $map['redis_igbinary'] = constant('\\Redis::SERIALIZER_IGBINARY');
        }

        if (defined('\\Redis::SERIALIZER_MSGPACK')) {
            $map['redis_msgpack'] = constant('\\Redis::SERIALIZER_MSGPACK');
        }

        if (defined('\\Redis::SERIALIZER_JSON')) {
            $map['redis_json'] = constant('\\Redis::SERIALIZER_JSON');
        }

        $serializer = mb_strtolower($this->defaultSerializer);

        if (true === isset($map[$serializer])) {
            $this->defaultSerializer = '';
            $connection->setOption(RedisService::OPT_SERIALIZER, $map[$serializer]);
        }

        $this->initSerializer();
    }
}
