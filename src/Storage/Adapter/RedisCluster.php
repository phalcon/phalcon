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

use Phalcon\Contracts\Storage\StorageTypes;
use Phalcon\Storage\Exceptions\ClusterConnectionFailed;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Support\Exception as SupportException;
use Redis as RedisConsts;
use RedisCluster as RedisService;
use Throwable;

use function defined;
use function mb_strtolower;

/**
 * RedisCluster adapter
 *
 * Capabilities (in addition to Redis):
 * - Counters: native atomic (incrBy()/decrBy()).
 * - getKeys(): blocking KEYS across all master nodes (per-node SCAN is left to
 *   the redesign); clear() flushes every master.
 * - Serializers: Phalcon-side, or backend-native via OPT_SERIALIZER.
 *
 * @phpstan-import-type storage_keys from StorageTypes
 * @phpstan-import-type storage_options from StorageTypes
 * @phpstan-import-type storage_rediscluster_options from StorageTypes
 * @phpstan-import-type storage_rediscluster_settings from StorageTypes
 *
 * @phpstan-property RedisService|null $adapter
 * @phpstan-property storage_rediscluster_settings $options
 */
class RedisCluster extends Redis
{
    protected string $prefix = 'ph-redc-';

    /**
     * You can create and connect to a cluster either by passing it one or more
     * 'seed' nodes, or by defining these in redis.ini as a 'named' cluster.
     *
     * If you are connecting with the cluster by offering a name, that is
     * configured in redis.ini:
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
     * you should pass hosts as an array,
     * eg. `$options = ["hosts" => ["a-host:7000", "b-host:7001"]]`.
     *
     * You can provide authentication data offering a string `user=password`
     * or array `["user" => "name", "password" => "secret"]`.
     *
     * The `timeout` is the amount of time library will wait when connecting
     * or writing to the cluster. `readTimeout` is the amount of time library
     * will wait for a result from the cluster.
     *
     * The `context` is an array of values used for ssl/tls stream context
     * options eg `["verify_peer" => 0, "local_cert" => "file:///path/to/cert.pem"]`
     *
     * @param array $options = [
     *                       "name"        => null,
     *                       "hosts"       => ["127.0.0.1:6379"],
     *                       "timeout"     => 0,
     *                       "readTimeout" => 0,
     *                       "persistent"  => false,
     *                       "auth"        => "",
     *                       "context"     => null,
     *                       ]
     *
     * @phpstan-param storage_rediscluster_options $options
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
     * @throws ClusterConnectionFailed|SupportException
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
     * @throws ClusterConnectionFailed|SupportException
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
            } catch (Throwable $ex) {
                throw new ClusterConnectionFailed(
                    sprintf(
                        "Could not connect to the Redis Cluster server due to: %s",
                        $ex->getMessage()
                    ),
                    previous: $ex
                );
            }

            $connection->setOption(RedisConsts::OPT_PREFIX, $this->prefix);

            $this->setSerializer($connection);
            $this->adapter = $connection;
        }

        return $this->adapter;
    }

    /**
     * Returns all the keys stored
     *
     * RedisCluster::scan() iterates one node at a time, so the blocking KEYS
     * command is retained here (phpredis routes it across the masters). The
     * per-node SCAN migration is left to the storage redesign.
     *
     * @phpstan-return storage_keys
     *
     * @throws ClusterConnectionFailed|SupportException
     */
    public function getKeys(string $prefix = ''): array
    {
        /** @var storage_keys $keys */
        $keys = $this->getAdapter()->keys('*');

        return $this->getFilteredKeys($keys, $prefix);
    }

    /**
     * @phpstan-param storage_options $options
     *
     * @phpstan-return storage_options
     */
    protected function getDefaultOptions(array $options): array
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
