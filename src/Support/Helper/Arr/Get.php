<?php

/**
 * This file is part of the Phalcon.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Support\Helper\Arr;

use function settype;

/**
 * Gets an array element by key and if it does not exist returns the default.
 * It also allows for casting the returned value to a specific type using
 * `settype` internally
 */
class Get
{
    /**
     * @param array<int|string,mixed> $collection
     * @param mixed                   $index
     * @param mixed|null              $defaultValue
     * @param string|null             $cast
     *
     * @return mixed|null
     */
    public function __invoke(
        array $collection,
        $index,
        $defaultValue = null,
        string $cast = null
    ) {
        $value = $collection[$index] ?? $defaultValue;

        if (null !== $cast) {
            settype($value, $cast);
        }

        return $value;
    }
}
