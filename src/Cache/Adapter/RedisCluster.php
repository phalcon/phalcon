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

namespace Phalcon\Cache\Adapter;

use Phalcon\Cache\Adapter\AdapterInterface as CacheAdapterInterface;
use Phalcon\Storage\Adapter\RedisCluster as StorageRedisCluster;

/**
 * RedisCluster adapter
 */
class RedisCluster extends StorageRedisCluster implements CacheAdapterInterface
{
    /**
     * EventType prefix.
     *
     * @var string
     */
    protected string $eventType = "cache";
}
