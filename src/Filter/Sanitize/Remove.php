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

namespace Phalcon\Filter\Sanitize;

use function str_replace;

/**
 * Phalcon\Filter\Sanitize\Remove
 *
 * Sanitizes a value removing parts of a string
 */
class Remove
{
    /**
     * @param array|string $input
     * @param array|string $replace
     *
     * @return string|string[]
     */
    public function __invoke($input, $replace)
    {
        return str_replace($replace, "", $input);
    }
}
