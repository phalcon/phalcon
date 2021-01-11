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
 *
 * @package Phalcon\Filter
 */
interface FilterInterface
{
    /**
     * Sanitizes a value with a specified single or set of sanitizers
     *
     * @param mixed $value
     * @param mixed $sanitizers
     * @param bool  $noRecursive
     *
     * @return mixed
     */
    public function sanitize($value, $sanitizers, bool $noRecursive = false);
}
