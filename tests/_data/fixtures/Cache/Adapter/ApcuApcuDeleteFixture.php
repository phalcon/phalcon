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

namespace Phalcon\Tests\Fixtures\Cache\Adapter;

use DateInterval;
use Phalcon\Cache\Adapter\Apcu;
use Phalcon\Cache\Adapter\Libmemcached as CacheLibmemcached;
use Phalcon\Storage\Exception;

class ApcuApcuDeleteFixture extends Apcu
{
    /**
     * @param string|array $key
     *
     * @return bool|array
     *
     * @link https://php.net/manual/en/function.apcu-delete.php
     */
    protected function phpApcuDelete($key)
    {
        return false;
    }
}
