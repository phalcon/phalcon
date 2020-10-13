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

use Phalcon\Di\Exception;

use function class_exists;

/**
 * Trait DiExceptionsTrait
 *
 * @package Phalcon\Di\Traits
 */
trait DiExceptionsTrait
{
    /**
     * @param string $name
     *
     * @throws Exception
     */
    private function checkClassExists(string $name): void
    {
        /**
         * The DI also acts as builder for any class even if it isn't
         * defined in the DI
         */
        if (true !== class_exists($name)) {
            throw new Exception(
                'Service "' . $name .
                '" was not found in the dependency injection container'
            );
        }
    }

    /**
     * @param string $name
     *
     * @throws Exception
     */
    private function throwCannotResolveService(string $name): void
    {
        throw new Exception(
            "Service '" . $name . "' cannot be resolved"
        );
    }

    /**
     * @param string $name
     *
     * @throws Exception
     */
    private function throwServiceNotFound(string $name): void
    {
        throw new Exception(
            "Service '" . $name .
            "' was not found in the dependency injection container"
        );
    }

    /**
     * @param string $method
     *
     * @throws Exception
     */
    private function throwUndefinedMethod(string $method): void
    {
        /**
         * The method doesn't start with set/get throw an exception
         */
        throw new Exception(
            "Call to undefined method or service '" . $method . "'"
        );
    }
}
