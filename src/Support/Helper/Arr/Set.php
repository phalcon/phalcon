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

/**
 * Sets an array element. Using a key is optional
 */
class Set
{
    /**
     * @param array<int|string,mixed> $collection
     * @param mixed                   $value
     * @param mixed                   $index
     *
     * @return array<int|string,mixed>
     */
    public function __invoke(
        array $collection,
        $value,
        $index = null
    ): array {
        $collection = $this->checkNull($collection, $value, $index);

        return $this->checkNotNull($collection, $value, $index);
    }

    /**
     * @param array<int|string,mixed> $collection
     * @param mixed                   $value
     * @param mixed                   $index
     *
     * @return array<int|string,mixed>
     */
    private function checkNull(array $collection, $value, $index): array
    {
        if (null === $index) {
            $collection[] = $value;
        }

        return $collection;
    }

    /**
     * @param array<int|string,mixed> $collection
     * @param mixed                   $value
     * @param mixed                   $index
     *
     * @return array<int|string,mixed>
     */
    private function checkNotNull(array $collection, $value, $index): array
    {
        if (null !== $index) {
            $collection[$index] = $value;
        }

        return $collection;
    }
}
