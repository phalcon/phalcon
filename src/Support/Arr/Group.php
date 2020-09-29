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

use function call_user_func;
use function function_exists;
use function is_callable;
use function is_object;
use function is_string;

/**
 * Class Group
 *
 * @package Phalcon\Support\Arr
 */
class Group
{
    /**
     * Groups the elements of an array based on the passed callable
     *
     * @param array           $collection
     * @param callable|string $method
     *
     * @return array
     */
    public function __invoke(array $collection, $method): array
    {
        $filtered = [];
        if (
            is_callable($method) ||
            (is_string($method) && function_exists($method))
        ) {
            foreach ($collection as $element) {
                $key              = call_user_func($method, $element);
                $filtered[$key][] = $element;
            }
        } else {
            foreach ($collection as $element) {
                if (is_object($element)) {
                    $key              = $element->{$method};
                    $filtered[$key][] = $element;
                } elseif (isset($element[$method])) {
                    $key              = $element[$method];
                    $filtered[$key][] = $element;
                }
            }
        }

        return $filtered;
    }
}
