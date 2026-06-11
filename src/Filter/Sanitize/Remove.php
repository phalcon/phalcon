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

use Phalcon\Contracts\Filter\Sanitizer;

use function str_replace;

/**
 * Sanitizes a value removing parts of a string
 */
class Remove implements Sanitizer
{
    /**
     * @param string[]|string $input
     * @param string[]|string $replace
     *
     * @return string|string[]
     */
    public function __invoke(array | string $input, array | string $replace)
    {
        return str_replace($replace, "", $input);
    }
}
