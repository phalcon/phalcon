<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Implementation of this component has been inspired by the queue-interop and
 * enqueue projects.
 *
 * @link    https://github.com/queue-interop/queue-interop
 * @license https://github.com/queue-interop/queue-interop/blob/master/LICENSE
 *
 * @link    https://github.com/php-enqueue/enqueue-dev
 * @license https://github.com/php-enqueue/enqueue-dev/blob/master/LICENSE
 */

declare(strict_types=1);

namespace Phalcon\Queue\Adapter\Redis;

use Phalcon\Contracts\Queue\ConnectionFactory as ConnectionFactoryInterface;
use Phalcon\Contracts\Queue\Context as ContextInterface;
use Phalcon\Queue\Exceptions\Exception;
use Redis as RedisService;

use function sprintf;

/**
 * Connects to a Redis server (ext-redis) and builds a RedisContext.
 *
 * Options:
 *   - host:         server host (default 127.0.0.1).
 *   - port:         server port (default 6379).
 *   - timeout:      connection timeout in seconds (default 0).
 *   - persistent:   use a persistent connection (default false).
 *   - persistentId: identifier for the persistent connection.
 *   - auth:         password, or [user, password] for ACL auth.
 *   - index:        database index to SELECT (default 0).
 *   - prefix:       key prefix for every queue (default "phalcon_queue:").
 *   - pollInterval: milliseconds between subscription poll passes (default 200).
 */
class RedisConnectionFactory implements ConnectionFactoryInterface
{
    public function __construct(protected array $options = [])
    {
    }

    public function createContext(): ContextInterface
    {
        $options      = $this->options;
        $host         = (string) ($options["host"] ?? "127.0.0.1");
        $port         = (int) ($options["port"] ?? 6379);
        $timeout      = (float) ($options["timeout"] ?? 0.0);
        $persistent   = (bool) ($options["persistent"] ?? false);
        $persistentId = (string) ($options["persistentId"] ?? "");
        $auth         = $options["auth"] ?? "";
        $index        = (int) ($options["index"] ?? 0);
        $prefix       = (string) ($options["prefix"] ?? "phalcon_queue:");
        $pollInterval = (int) ($options["pollInterval"] ?? 200);

        $redis = new RedisService();

        if ($persistent) {
            $parameter = !empty($persistentId) ? $persistentId : "persistentId" . $index;
            $result    = $redis->pconnect($host, $port, $timeout, $parameter);
        } else {
            $result = $redis->connect($host, $port, $timeout);
        }

        if (!$result) {
            throw new Exception(
                sprintf("Could not connect to the Redis server [%s:%s]", $host, $port)
            );
        }

        if (!empty($auth) && true !== $redis->auth($auth)) {
            throw new Exception("Failed to authenticate with the Redis server");
        }

        if ($index > 0 && true !== $redis->select($index)) {
            throw new Exception("Failed to select the Redis database index");
        }

        return new RedisContext($redis, $prefix, $pollInterval);
    }
}
