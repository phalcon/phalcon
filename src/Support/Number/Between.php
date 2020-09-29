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

namespace Phalcon\Support\Number;

/**
 * Class Between
 *
 * @package Phalcon\Support\Str
 */
class Between
{
    /**
     * Helper method to get an array element or a default
     *
     * @param int $value
     * @param int $start
     * @param int $end
     *
     * @return bool
     */
    public function __invoke(int $value, int $start, int $end): bool
    {
        return $value >= $start && $value <= $end;
    }
}
