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

namespace Phiz\Cache\Adapter;

use Phiz\Cache\Adapter\AdapterInterface as CacheAdapterInterface;
use Phiz\Storage\Adapter\Redis as StorageRedis;

/**
 * Redis adapter
 */
class Redis extends StorageRedis implements CacheAdapterInterface
{
}
