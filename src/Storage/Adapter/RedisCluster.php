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

use Phalcon\Storage\Exception as StorageException;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Support\Exception as SupportException;
use Redis as RedisConsts;
use RedisCluster as RedisService;
use Throwable;

use function defined;
use function mb_strtolower;

/**
 * Redis adapter
 *
 * @property array $options
 */
class RedisCluster extends Redis
{
    /**
     * @var string
     */
    protected string $prefix = 'ph-redc-';

    /**
     * You can create and connect to a cluster either by passing it one or more 'seed' nodes, or by defining
     * these in redis.ini as a 'named' cluster.
     *
     * If you are connecting with the cluster by offering a name, that is configured in redis.ini:
     *
     *      ```
     *      # In redis.ini
     *      redis.clusters.seeds = "mycluster[]=localhost:7000&test[]=localhost:7001"
     *      redis.clusters.timeout = "mycluster=5"
     *      redis.clusters.read_timeout = "mycluster=10"
     *      redis.clusters.auth = "mycluster=password"
     *      ```
     * you can use `$options = ["name" => "mycluster"]`.
     *
     * If you don't have cluster seeds configured in your redis.ini,
     * you should pass hosts as an array, eg. `$options = ["hosts" => ["a-host:7000", "b-host:7001"]]`.
     *
     * You can provide authentication data offering a string `user=password` or
     * array `["user" => "name", "password" => "secret"]`.
     *
     * The `timeout` is the amount of time library will wait when connecting or writing to the cluster
     * `readTimeout` is the amount of time library will wait for a result from the cluster.
     *
     * The `context` is an array of values used for ssl/tls stream context
     * options eg `["verify_peer" => 0, "local_cert" => "file:///path/to/cert.pem"]`
     *
     * @param SerializerFactory $factory
     * @param array             $options {
     *                                   name: string | null,
     *                                   hosts: array,
     *                                   timeout: float,
     *                                   readTimeout: float,
     *                                   persistent: bool,
     *                                   auth: string|array,
     *                                   context: string
     *                                   }
     *
     * @throws SupportException
     */
    public function __construct(SerializerFactory $factory, array $options = [])
    {
        parent::__construct($factory, $options);
    }

    /**
     * Flushes/clears the cache
     *
     * @return bool
     * @throws StorageException|SupportException
     */
    public function clear(): bool
    {
        $adapter = $this->getAdapter();
        foreach ($adapter->_masters() as $master) {
            $adapter->flushAll($master);
        }

        return true;
    }

    /**
     * Returns the already connected adapter or connects to the Redis
     * server(s)
     *
     * @return RedisService
     * @throws StorageException|SupportException
     */
    public function getAdapter(): mixed
    {
        if (null === $this->adapter) {
            $options = $this->options;

            try {
                $connection = new RedisService(
                    $options["name"],
                    $options["hosts"],
                    $options["timeout"],
                    $options["readTimeout"],
                    $options["persistent"],
                    $options["auth"],
                    $options["context"]
                );
            } catch (Throwable $e) {
                throw new StorageException(
                    sprintf(
                        "Could not connect to the Redis cluster server due to: %s",
                        $e->getMessage()
                    ),
                    previous: $e
                );
            }

            $connection->setOption(RedisConsts::OPT_PREFIX, $this->prefix);

            $this->setSerializer($connection);
            $this->adapter = $connection;
        }

        return $this->adapter;
    }


    protected function getDefaultOptions($options): array
    {
        /**
         * Lets set some defaults and options here
         */
        $options["name"]        = $options["name"] ?? null;
        $options["hosts"]       = $options["hosts"] ?? ["127.0.0.1:6379"];
        $options["timeout"]     = $options["timeout"] ?? 0;
        $options["readTimeout"] = $options["readTimeout"] ?? 0;
        $options["persistent"]  = (bool)($options["persistent"] ?? false);
        $options["auth"]        = $options["auth"] ?? "";
        $options["context"]     = $options["context"] ?? null;

        return $options;
    }

    /**
     * Checks the serializer. If it is a supported one it is set, otherwise
     * the custom one is set.
     *
     * @param RedisService $connection
     *
     * @throws SupportException
     */
    private function setSerializer(RedisService $connection): void
    {
        $map = [
            'redis_none' => RedisConsts::SERIALIZER_NONE,
            'redis_php'  => RedisConsts::SERIALIZER_PHP,
        ];

        /**
         * In case IGBINARY or MSGPACK are not defined for previous versions
         * of Redis
         */
        if (defined('\\Redis::SERIALIZER_IGBINARY')) {
            $map['redis_igbinary'] = RedisConsts::SERIALIZER_IGBINARY;
        }

        if (defined('\\Redis::SERIALIZER_MSGPACK')) {
            $map['redis_msgpack'] = RedisConsts::SERIALIZER_MSGPACK;
        }

        if (defined('\\Redis::SERIALIZER_JSON')) {
            $map['redis_json'] = RedisConsts::SERIALIZER_JSON;
        }

        $serializer = mb_strtolower($this->defaultSerializer);

        if (isset($map[$serializer])) {
            $this->defaultSerializer = '';
            $connection->setOption(RedisConsts::OPT_SERIALIZER, $map[$serializer]);
        }

        $this->initSerializer();
    }
}
