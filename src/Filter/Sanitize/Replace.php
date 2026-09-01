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
 * Sanitizes a value replacing parts of a string
 */
class Replace implements Sanitizer
{
    /**
     * @param string|string[] $input
     * @param string|string[] $from
     * @param string|string[] $to
     *
     * @return string|string[]
     */
    public function __invoke(mixed $input, mixed $from, mixed $to)
    {
        return str_replace($from, $to, $input);
    }
}
