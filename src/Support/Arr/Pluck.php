<?php

/**
 * This file is part of the Phalcon.
 *
 * (c) Phalcon Team <team@phalcon.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Support\Arr;

use function is_array;
use function is_object;

/**
 * Class Pluck
 *
 * @package Phalcon\Support\Arr
 */
class Pluck
{
    /**
     * Retrieves all of the values for a given key:
     *
     * @param array  $collection
     * @param string $element
     *
     * @return array
     */
    public function __invoke(array $collection, string $element): array
    {
        $filtered = [];
        foreach ($collection as $item) {
            if (is_object($item) && isset($item->{$element})) {
                $filtered[] = $item->{$element};
            } elseif (is_array($item) && isset($item[$element])) {
                $filtered[] = $item[$element];
            }
        }

        return $filtered;
    }
}
