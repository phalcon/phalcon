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

namespace Phalcon\Support\Traits;

use function apcu_dec;
use function apcu_delete;
use function apcu_exists;
use function apcu_fetch;
use function apcu_inc;
use function apcu_store;

/**
 * Trait PhpApcuTrait
 *
 * @package Phalcon\Storage\Adapter\Traits
 */
trait PhpApcuTrait
{
    /**
     * @param string    $key
     * @param int       $step
     * @param bool|null $success
     * @param int       $ttl
     *
     * @return int|false
     *
     * @link https://php.net/manual/en/function.apcu-dec.php
     */
    protected function phpApcuDec($key, $step = 1, &$success = null, $ttl = 0)
    {
        return apcu_dec($key, $step, $success, $ttl);
    }

    /**
     * @param string|array $key
     *
     * @return bool|array
     *
     * @link https://php.net/manual/en/function.apcu-delete.php
     */
    protected function phpApcuDelete($key)
    {
        return apcu_delete($key);
    }

    /**
     * @param string|array $key
     *
     * @return bool|array
     *
     * @link https://php.net/manual/en/function.apcu-exists.php
     */
    protected function phpApcuExists($key)
    {
        return apcu_exists($key);
    }

    /**
     * @param string|array $key
     * @param bool|null    $success
     *
     * @return mixed|false
     *
     * @link https://php.net/manual/en/function.apcu-fetch.php
     */
    protected function phpApcuFetch($key, &$success = null)
    {
        return apcu_fetch($key, $success);
    }

    /**
     * @param string    $key
     * @param int       $step
     * @param bool|null $success
     * @param int       $ttl
     *
     * @return false|int
     *
     * @link https://php.net/manual/en/function.apcu-inc.php
     */
    protected function phpApcuInc($key, $step = 1, &$success = null, $ttl = 0)
    {
        return apcu_inc($key, $step, $success, $ttl);
    }

    /**
     * @param string|array $key
     * @param mixed        $var
     * @param int          $ttl
     *
     * @return bool|array
     *
     * @link https://php.net/manual/en/function.apcu-store.php
     */
    protected function phpApcuStore($key, $var, $ttl = 0)
    {
        return apcu_store($key, $var, $ttl);
    }
}
