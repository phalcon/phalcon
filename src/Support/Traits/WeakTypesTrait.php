<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phalcon\Support\Traits;

use Error;
use TypeError;

use function call_user_func;
use function call_user_func_array;
use function explode;
use function is_array;
use function is_callable;
use function is_string;
use function trigger_error;

use const E_USER_WARNING;

/**
 * Calls a setter or writes a property with the PHP weak type rules.
 *
 * This file has no declare(strict_types=1). Do not add it. PHP checks a call
 * or a property write in the mode of the file that holds the calling code.
 * cphalcon is compiled C, so PHP checks its setter calls and property writes
 * in weak mode. These methods give the same checks to the PHP code. The
 * WeakModeTypesTest tests fail if the declare is added.
 */
trait WeakTypesTrait
{
    /**
     * Returns true if the value can be called. If not, gives a warning with
     * the reason of PHP and returns false. The caller then returns null.
     *
     * This is the same as call_user_func_array() in cphalcon. The level is
     * E_USER_WARNING, not E_WARNING: PHP code cannot give E_WARNING.
     *
     * @phpstan-assert-if-true callable $callable
     */
    private static function isCallableOrWarn(mixed $callable): bool
    {
        if (is_callable($callable)) {
            return true;
        }

        try {
            /**
             * The value cannot be called, thus this throws before a call.
             *
             * @var callable $callable
             */
            call_user_func_array($callable, []);
        } catch (TypeError $ex) {
            $message = $ex->getMessage();

            trigger_error(
                explode('must be a valid callback, ', $message, 2)[1] ?? $message,
                E_USER_WARNING
            );
        }

        return false;
    }

    /**
     * Calls a method of the object with one value, in weak mode.
     */
    private static function weakCall(
        object $object,
        string $method,
        mixed $value
    ): void {
        $object->$method($value);
    }

    /**
     * Calls a callable with a list of arguments, in weak mode, and returns
     * the result. A string key gives a named argument.
     *
     * @param array<array-key, mixed> $arguments
     */
    private static function weakCallArray(
        callable $callable,
        array $arguments
    ): mixed {
        return call_user_func_array($callable, $arguments);
    }

    /**
     * Calls a method of the object with a list of arguments, in weak mode,
     * and returns the result. If the method cannot be called from this
     * scope, the direct call gives the same Error as cphalcon.
     *
     * @param array<array-key, mixed> $arguments
     */
    private static function weakCallMethod(
        object $object,
        string $method,
        array $arguments
    ): mixed {
        $callable = [$object, $method];
        if (!is_callable($callable)) {
            return $object->$method(...$arguments);
        }

        return call_user_func_array($callable, $arguments);
    }

    /**
     * Calls the value with call_user_func(), in weak mode, and returns the
     * result. This is the same as call_user_func() in cphalcon. A value that
     * cannot be called throws the TypeError of call_user_func().
     */
    private static function weakCallUserFunc(
        mixed $callable,
        mixed ...$arguments
    ): mixed {
        /** @var callable $callable */
        return call_user_func($callable, ...$arguments);
    }

    /**
     * Calls the value with a list of arguments, in weak mode, and returns the
     * result. This is the same as a dynamic call (`{value}(...)`) in
     * cphalcon. A value that cannot be called throws Error with the cphalcon
     * message.
     *
     * @param array<array-key, mixed> $arguments
     */
    private static function weakInvoke(
        mixed $callable,
        array $arguments
    ): mixed {
        /**
         * For an array, the direct call below gives the same Error as
         * cphalcon. For other values, cphalcon has its own message.
         */
        if (!is_array($callable) && !is_callable($callable)) {
            throw new Error(
                'Call to undefined function '
                . (is_string($callable) ? $callable : 'undefined')
                . '()'
            );
        }

        /** @var callable $callable */
        return $callable(...$arguments);
    }

    /**
     * Creates an instance of the class with a list of constructor arguments,
     * in weak mode. A string key gives a named argument.
     *
     * @param array<array-key, mixed> $arguments
     */
    private static function weakNew(
        string $className,
        array $arguments
    ): object {
        return new $className(...$arguments);
    }

    /**
     * Returns the value as a bool, in weak mode. cphalcon does not check its
     * return types and returns the value with no change. Here, a scalar value
     * becomes a bool, and an array or an object gives TypeError.
     */
    private static function weakReturnBool(mixed $value): bool
    {
        return $value;
    }

    /**
     * Writes a property of the object, in weak mode.
     */
    private static function weakWrite(
        object $object,
        string $property,
        mixed $value
    ): void {
        $object->$property = $value;
    }
}
