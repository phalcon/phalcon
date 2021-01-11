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

namespace Phalcon\Di\Traits;

use function call_user_func;
use function call_user_func_array;

/**
 * Trait DiInstanceTrait
 *
 * @package Phalcon\Di\Traits
 */
trait DiInstanceTrait
{
    /**
     * @param string     $name
     * @param array|null $parameters
     *
     * @return mixed
     */
    private function createInstance(string $name, array $parameters = null)
    {
        if (true !== empty($parameters)) {
            return new $name(...$parameters);
        }

        return new $name();
    }

    /**
     * @param mixed      $instance
     * @param array|null $parameters
     *
     * @return mixed
     */
    private function createClosureInstance($instance, array $parameters = null)
    {
        if (true !== empty($parameters)) {
            return call_user_func_array($instance, $parameters);
        }

        return call_user_func($instance);
    }
}
