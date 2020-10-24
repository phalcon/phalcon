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

use function function_exists;

/**
 * Trait PhpFunctionTrait
 *
 * @package Phalcon\Support\Traits
 */
trait PhpFunctionTrait
{
    /**
     * Return true if the given function has been defined
     *
     * @param string $function
     *
     * @return bool
     *
     * @link https://php.net/manual/en/function.function-exists.php
     */
    protected function phpFunctionExists($function)
    {
        return function_exists($function);
    }

    /**
     * Gets the value of a configuration option
     *
     * @param string $varname
     *
     * @return string
     *
     * @link https://php.net/manual/en/function.ini-get.php
     * @link https://php.net/manual/en/ini.list.php
     */
    protected function phpIniGet($varname): string
    {
        return ini_get($varname);
    }
}
