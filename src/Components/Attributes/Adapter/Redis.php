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

namespace Phalcon\Components\Attributes\Adapter;

use Phalcon\Storage\Adapter\Redis as StorageRedis;

/**
 * Stores the parsed annotations in redis.
 */
class Redis extends StorageRedis implements AdapterInterface
{
}
