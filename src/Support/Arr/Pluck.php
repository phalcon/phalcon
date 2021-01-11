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
            $filtered = $this->checkObject($filtered, $element, $item);
            $filtered = $this->checkArray($filtered, $element, $item);
        }

        return $filtered;
    }

    /**
     * @param array  $filtered
     * @param string $element
     * @param mixed  $item
     *
     * @return array
     */
    private function checkArray(array $filtered, string $element, $item): array
    {
        if (true === is_array($item) && isset($item[$element])) {
            $filtered[] = $item[$element];
        }

        return $filtered;
    }

    /**
     * @param array  $filtered
     * @param string $element
     * @param mixed  $item
     *
     * @return array
     */
    private function checkObject(array $filtered, string $element, $item): array
    {
        if (true === is_object($item) && isset($item->{$element})) {
            $filtered[] = $item->{$element};
        }

        return $filtered;
    }
}
