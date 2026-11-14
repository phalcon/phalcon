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

/**
 * Calls a setter or writes a property with the PHP weak type rules.
 *
 * This file has no declare(strict_types=1). Do not add it. PHP checks a call
 * or a property write in the mode of the file that holds the calling code.
 * cphalcon is compiled C, so PHP checks its setter calls and property writes
 * in weak mode. These methods give the same checks to the PHP code. The tests
 * in tests/database/Mvc/Model/WeakModeTypesTest.php fail if the declare is
 * added.
 */
trait WeakTypesTrait
{
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
