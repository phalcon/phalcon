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

use function is_array;
use function is_object;

/**
 * Returns a subset of the collection based on the values of the collection
 */
class Pluck
{
    /**
     * @param array<int|string,mixed> $collection
     * @param string                  $element
     *
     * @return array<int|string,mixed>
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
     * @param array<int|string,mixed> $filtered
     * @param string                  $element
     * @param mixed                   $item
     *
     * @return array<int|string,mixed>
     */
    private function checkArray(array $filtered, string $element, $item): array
    {
        if (true === is_array($item) && isset($item[$element])) {
            $filtered[] = $item[$element];
        }

        return $filtered;
    }

    /**
     * @param array<int|string,mixed> $filtered
     * @param string                  $element
     * @param mixed                   $item
     *
     * @return array<int|string,mixed>
     */
    private function checkObject(array $filtered, string $element, $item): array
    {
        if (true === is_object($item) && isset($item->{$element})) {
            $filtered[] = $item->{$element};
        }

        return $filtered;
    }
}
