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

namespace Phalcon\Collection;

/**
 * Phalcon\Collection\ReadOnly is a read only Collection object
 */
class ReadOnly extends Collection
{
    /**
     * Delete the element from the collection
     *
     * @param string $element
     *
     * @throws Exception
     */
    public function remove(string $element): void
    {
        throw new Exception('The object is read only');
    }

    /**
     * Set an element in the collection
     *
     * @param string $element
     * @param mixed  $value
     *
     * @throws Exception
     */
    public function set(string $element, $value): void
    {
        throw new Exception('The object is read only');
    }
}
