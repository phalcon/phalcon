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

namespace Phiz\Filter\Sanitize;

use function str_replace;

/**
 * Phiz\Filter\Sanitize\Replace
 *
 * Sanitizes a value replacing parts of a string
 */
class Replace
{
    /**
     * @param  mixed $input
     * @param  mixed $source
     * @param  mixed $target
     *
     * @return string|string[]
     */
    public function __invoke($input, $source, $target)
    {
        return str_replace($source, $target, $input);
    }
}
