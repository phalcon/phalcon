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

use function array_merge;
use function array_values;
use function is_array;

/**
 * Class Flatten
 *
 * @package Phalcon\Support\Arr
 */
class Flatten
{
    /**
     * Flattens an array up to the one level depth, unless `$deep` is set to
     * `true`
     *
     * @param array $collection
     * @param bool  $deep
     *
     * @return array
     */
    public function __invoke(array $collection, bool $deep = false): array
    {
        $data = [];

        foreach ($collection as $item) {
            $data = $this->processNotArray($data, $item);
            $data = $this->processArrayDeep($data, $item, $deep);
            $data = $this->processArray($data, $item, $deep);
        }

        return $data;
    }

    /**
     * @param array $data
     * @param mixed $item
     *
     * @return array
     */
    private function processNotArray(array $data, $item): array
    {
        if (true !== is_array($item)) {
            $data[] = $item;
        }

        return $data;
    }

    /**
     * @param array $data
     * @param mixed $item
     * @param bool  $deep
     *
     * @return array
     */
    private function processArray(array $data, $item, bool $deep): array
    {
        if (true === is_array($item) && true !== $deep) {
            $data = array_merge($data, array_values($item));
        }

        return $data;
    }

    /**
     * @param array $data
     * @param mixed $item
     * @param bool  $deep
     *
     * @return array
     */
    private function processArrayDeep(array $data, $item, bool $deep): array
    {
        if (true === is_array($item) && true === $deep) {
            $data = array_merge($data, $this->__invoke($item, true));
        }

        return $data;
    }
}
