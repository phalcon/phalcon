<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Implementation of this file has been influenced by Nyholm/psr7 and Laminas
 *
 * @link    https://github.com/Nyholm/psr7
 * @license https://github.com/Nyholm/psr7/blob/master/LICENSE
 * @link    https://github.com/laminas/laminas-diactoros
 * @license https://github.com/zendframework/zend-diactoros/blob/master/LICENSE.md
 */

namespace Phalcon\Http\Message;

use Phalcon\Http\Message\Exception\InvalidArgumentException;

/**
 * Common methods
 */
abstract class AbstractCommon
{
    /**
     * Checks the element passed if it is a string
     *
     * @param mixed $element
     */
    final protected function checkStringParameter($element): void
    {
        if (true !== is_string($element)) {
            throw new InvalidArgumentException(
                "Method requires a string argument"
            );
        }
    }

    /**
     * Returns a new instance having set the parameter
     *
     * @param mixed  $element
     * @param string $property
     *
     * @return static
     */
    final protected function cloneInstance($element, string $property)
    {
        /**
         * No change - return the same object
         */
        if ($element === $this->$property) {
            return $this;
        }

        $newInstance            = clone $this;
        $newInstance->$property = $element;

        return $newInstance;
    }
}
