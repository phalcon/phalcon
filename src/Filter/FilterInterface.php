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

namespace Phalcon\Filter;

/**
 * Interface FilterInterface
 */
interface FilterInterface
{
    /**
     * Get a service. If it is not in the mapper array, create a new object,
     * set it and then return it.
     *
     * @param string $name
     *
     * @return mixed
     * @throws Exception
     */
    public function get(string $name): mixed;

    /**
     * Checks if a service exists in the map array
     *
     * @param string $name
     *
     * @return bool
     */
    public function has(string $name): bool;

    /**
     * Sanitizes a value with a specified single or set of sanitizers
     *
     * @param mixed        $value
     * @param array|string $sanitizers
     * @param bool         $noRecursive
     *
     * @return mixed
     */
    public function sanitize(
        mixed $value,
        array | string $sanitizers,
        bool $noRecursive = false
    ): mixed;

    /**
     * Set a new service to the mapper array
     *
     * @param string $name
     * @param mixed  $service
     */
    public function set(string $name, $service): void;
}
