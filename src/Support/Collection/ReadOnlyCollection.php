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

namespace Phalcon\Support\Collection;

use Phalcon\Support\Collection;

/**
 * A read only Collection object
 */
class ReadOnlyCollection extends Collection
{
    /**
     * Delete the element from the collection
     *
     * @param string $element Name of the element
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
     * @param string $element Name of the element
     * @param mixed  $value   Value to store for the element
     *
     * @throws Exception
     */
    public function set(string $element, $value): void
    {
        throw new Exception('The object is read only');
    }
}
