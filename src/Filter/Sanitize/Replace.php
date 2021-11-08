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
 * Phalcon\Filter\Sanitize\Replace
 *
 * Sanitizes a value replacing parts of a string
 */
class Replace
{
    /**
     * @param array|string $input
     * @param array|string $source
     * @param array|string $target
     *
     * @return string|string[]
     */
    public function __invoke($input, $source, $target)
    //public function __invoke(array | string $input, array | string $source, array | string $target)
    {
        return str_replace($source, $target, $input);
    }
}
